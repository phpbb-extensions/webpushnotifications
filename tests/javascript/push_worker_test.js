'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const workerSource = fs.readFileSync(process.argv[2], 'utf8');

async function clickNotification(notificationUrl, workerUrl) {
	const listeners = {};
	const openedUrls = [];
	const self = {
		location: new URL(workerUrl),
		clients: {
			claim() {},
			openWindow(url) {
				openedUrls.push(url);
				return Promise.resolve(null);
			},
		},
		registration: {
			showNotification() {},
			update() {},
		},
		skipWaiting() {},
		addEventListener(type, listener) {
			listeners[type] = listener;
		},
	};

	vm.runInNewContext(workerSource, {console, self, URL}, {filename: process.argv[2]});

	let pending;
	listeners.notificationclick({
		notification: {
			data: {url: notificationUrl},
			close() {},
		},
		waitUntil(promise) {
			pending = promise;
		},
	});

	if (pending) {
		await pending;
	}

	return openedUrls;
}

async function run() {
	const subfolderWorker = 'http://localhost:8090/forum/app.php/user/push/worker';
	const notificationUrl = './../../../ucp.php?i=pm&mode=view&p=4';

	assert.deepEqual(
		await clickNotification(notificationUrl, subfolderWorker),
		['http://localhost:8090/forum/ucp.php?i=pm&mode=view&p=4'],
		'relative notification URLs must resolve from worker route on subfolder boards',
	);
	assert.deepEqual(
		await clickNotification(notificationUrl, 'http://localhost:8090/app.php/user/push/worker'),
		['http://localhost:8090/ucp.php?i=pm&mode=view&p=4'],
		'relative notification URLs must still work on root boards',
	);
	assert.deepEqual(
		await clickNotification('https://example.com/topic/1', subfolderWorker),
		['https://example.com/topic/1'],
		'absolute HTTPS notification URLs must remain unchanged',
	);
	assert.deepEqual(
		await clickNotification('http://example.com/topic/1', subfolderWorker),
		['http://example.com/topic/1'],
		'absolute HTTP notification URLs must remain unchanged',
	);
	assert.deepEqual(
		await clickNotification('javascript:alert(1)', subfolderWorker),
		[],
		'non-HTTP notification URLs must be rejected',
	);
	assert.deepEqual(
		await clickNotification('http://[invalid', subfolderWorker),
		[],
		'malformed notification URLs must be ignored',
	);
}

run().catch(error => {
	console.error(error);
	process.exitCode = 1;
});
