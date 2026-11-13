'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const webpushSource = fs.readFileSync(process.argv[2], 'utf8');
const endpoint = 'https://push.example.test/subscription/old';
const currentVapidKey = 'AQID';

function createButton(id) {
	const listeners = {};
	return {
		id,
		disabled: false,
		hidden: false,
		type: 'button',
		classList: {
			contains() {
				return false;
			},
			toggle(name, state) {
				if (name === 'hidden') {
					this.owner.hidden = state;
				}
			},
		},
		addEventListener(type, listener) {
			listeners[type] = listener;
		},
		removeEventListener(type) {
			delete listeners[type];
		},
		getAttribute() {
			return 'Web Push error';
		},
		listeners,
	};
}

async function settle() {
	await new Promise(resolve => setImmediate(resolve));
	await new Promise(resolve => setImmediate(resolve));
}

async function runScenario({stored = [], expirationTime = 0, applicationServerKey = [1, 2, 3], click = false}) {
	const calls = {fetch: 0, subscribe: 0, unsubscribe: 0};
	const subscribeButton = createButton('subscribe_webpush');
	const unsubscribeButton = createButton('unsubscribe_webpush');
	subscribeButton.classList.owner = subscribeButton;
	unsubscribeButton.classList.owner = unsubscribeButton;

	const popup = {
		style: {display: 'none'},
		addEventListener() {},
	};
	const storage = new Map();
	let activeSubscription;

	function makeSubscription(subscriptionEndpoint, key, expires = 0) {
		return {
			endpoint: subscriptionEndpoint,
			expirationTime: expires,
			options: {applicationServerKey: Uint8Array.from(key).buffer},
			toJSON() {
				return {
					endpoint: this.endpoint,
					expirationTime: this.expirationTime,
					keys: {p256dh: 'public-key', auth: 'auth-token'},
				};
			},
			async unsubscribe() {
				calls.unsubscribe++;
				if (activeSubscription === this) {
					activeSubscription = null;
				}
				return true;
			},
		};
	}

	activeSubscription = makeSubscription(endpoint, applicationServerKey, expirationTime);
	const registration = {
		pushManager: {
			async getSubscription() {
				return activeSubscription;
			},
			async subscribe() {
				calls.subscribe++;
				activeSubscription = makeSubscription('https://push.example.test/subscription/new', [1, 2, 3]);
				return activeSubscription;
			},
		},
	};

	const document = {
		readyState: 'complete',
		querySelectorAll(selector) {
			return selector.includes('unsubscribe') ? [unsubscribeButton] : [subscribeButton];
		},
		querySelector() {
			return null;
		},
		getElementById(id) {
			return id === 'wpn_popup_prompt' ? popup : null;
		},
		addEventListener() {},
		removeEventListener() {},
	};
	const window = {
		isSecureContext: true,
		PushManager: function PushManager() {},
		atob(value) {
			return Buffer.from(value, 'base64').toString('binary');
		},
		btoa(value) {
			return Buffer.from(value, 'binary').toString('base64');
		},
	};
	const phpbb = {
		alert() {},
		alertTime: 0,
		loadingIndicator() {
			return {fadeOut() {}};
		},
	};
	const context = {
		console,
		document,
		fetch: async() => {
			calls.fetch++;
			return {
				async json() {
					return {success: true, form_tokens: {creation_time: 2, form_token: 'new-token'}};
				},
			};
		},
		FormData: class FormData {
			append() {}
		},
		localStorage: {
			getItem(key) {
				return storage.get(key) || null;
			},
			setItem(key, value) {
				storage.set(key, value);
			},
			removeItem(key) {
				storage.delete(key);
			},
		},
		navigator: {
			serviceWorker: {
				async register() {
					return registration;
				},
				async getRegistration() {
					return registration;
				},
			},
		},
		Notification: {
			permission: 'granted',
			async requestPermission() {
				return 'granted';
			},
		},
		phpbb,
		phpbbWebpushOptions: {
			serviceWorkerUrl: '/push-worker',
			subscribeUrl: '/subscribe',
			unsubscribeUrl: '/unsubscribe',
			togglePopupUrl: '/toggle-popup',
			ajaxErrorTitle: 'Error',
			unsubscribeError: 'Unsubscribe error',
			vapidPublicKey: currentVapidKey,
			formTokens: {creationTime: 1, formToken: 'token'},
			subscriptions: stored.map(storedEndpoint => ({endpoint: storedEndpoint, expirationTime: 0})),
		},
		setTimeout(callback) {
			callback();
		},
		Uint8Array,
		window,
	};

	vm.runInNewContext(webpushSource, context, {filename: process.argv[2]});
	await settle();

	if (click) {
		await subscribeButton.listeners.click({
			currentTarget: subscribeButton,
			preventDefault() {},
			stopPropagation() {},
		});
		await settle();
	}

	return {calls, popup, subscribeButton, unsubscribeButton};
}

async function run() {
	const foreign = await runScenario({stored: []});
	assert.deepEqual(foreign.calls, {fetch: 0, subscribe: 0, unsubscribe: 0});
	assert.equal(foreign.subscribeButton.hidden, false, 'foreign subscription must leave subscribe control visible');
	assert.equal(foreign.unsubscribeButton.hidden, true, 'foreign subscription must not appear owned');
	assert.equal(foreign.popup.style.display, 'flex', 'foreign subscription must use normal opt-in prompt');

	const owned = await runScenario({stored: [endpoint]});
	assert.deepEqual(owned.calls, {fetch: 0, subscribe: 0, unsubscribe: 0});
	assert.equal(owned.subscribeButton.hidden, true, 'owned valid subscription must appear subscribed');
	assert.equal(owned.unsubscribeButton.hidden, false, 'owned valid subscription must allow unsubscribe');

	const staleKey = await runScenario({stored: [endpoint], applicationServerKey: [4, 5, 6]});
	assert.deepEqual(staleKey.calls, {fetch: 1, subscribe: 1, unsubscribe: 1}, 'owned stale VAPID subscription must refresh');

	const expired = await runScenario({stored: [endpoint], expirationTime: Date.now() - 1000});
	assert.deepEqual(expired.calls, {fetch: 1, subscribe: 1, unsubscribe: 1}, 'owned expired subscription must refresh');

	const explicit = await runScenario({stored: [], click: true});
	assert.deepEqual(explicit.calls, {fetch: 1, subscribe: 1, unsubscribe: 1}, 'explicit opt-in may replace foreign subscription');
}

run().catch(error => {
	console.error(error);
	process.exitCode = 1;
});
