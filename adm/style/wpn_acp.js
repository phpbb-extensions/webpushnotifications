/* global phpbb */

/**
 * Convert raw key ArrayBuffer to base64 string.
 *
 * @param {ArrayBuffer} rawKey Raw key array buffer as exported by SubtleCrypto exportKey()
 * @returns {string} Base64 encoded raw key string
 */
phpbb.rawKeyToBase64 = (rawKey) => {
	'use strict';
	const keyBuffer = new Uint8Array(rawKey);
	let keyText = '';
	const keyLength = keyBuffer.byteLength;
	for (let i = 0; i < keyLength; i++) {
		keyText += String.fromCharCode(keyBuffer[i]);
	}

	return window.btoa(keyText);
};

/**
 * Base64URL encode base64 encoded string
 *
 * @param {string} base64String Base64 encoded string
 * @returns {string} Base64URL encoded string
 */
phpbb.base64UrlEncode = (base64String) => {
	'use strict';
	return base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
};

/**
 * Generate a VAPID key pair with public and private key strings.
 *
 * @returns {Promise<{privateKey: string, publicKey: string}>}
 */
phpbb.generateVAPIDKeys = async() => {
	'use strict';
	const keyPair = await window.crypto.subtle.generateKey(
		{
			name: 'ECDH',
			namedCurve: 'P-256',
		},
		true,
		[ 'deriveKey', 'deriveBits' ]
	);

	const privateKeyJwk = await window.crypto.subtle.exportKey('jwk', keyPair.privateKey);
	const publicKeyBuffer = await window.crypto.subtle.exportKey('raw', keyPair.publicKey);

	return {
		privateKey: privateKeyJwk.d,
		publicKey: phpbb.base64UrlEncode(phpbb.rawKeyToBase64(publicKeyBuffer))
	};
};

document.addEventListener('DOMContentLoaded', () => {
	'use strict';
	const credentials = document.querySelector('#vapid-credentials');
	const generateButton = document.querySelector('#generate-vapid-keys');
	const publicKeyInput = document.querySelector('#webpush_vapid_public');
	const privateKeyInput = document.querySelector('#webpush_vapid_private');
	const togglePrivateButton = document.querySelector('#toggle-vapid-private');
	const statusBadge = document.querySelector('#vapid-status-badge');
	const generationStatus = document.querySelector('#vapid-generation-status');
	const settingsForm = document.querySelector('#webpushnotifications_settings');
	const confirmationTemplate = document.querySelector('#vapid-rotation-confirm-template');
	const confirmationDialog = document.querySelector('#phpbb_confirm');

	if (!credentials || !generateButton || !publicKeyInput || !privateKeyInput || !confirmationTemplate || !confirmationDialog) {
		return;
	}

	const initialBadgeText = statusBadge.textContent.trim();
	const initialBadgeClass = statusBadge.className;

	const hidePrivateKey = () => {
		privateKeyInput.type = 'password';
		togglePrivateButton.setAttribute('aria-pressed', 'false');
		togglePrivateButton.setAttribute('aria-label', togglePrivateButton.dataset.showLabel);
		togglePrivateButton.querySelector('.icon').className = 'icon fa-eye fa-fw';
	};

	togglePrivateButton.addEventListener('click', () => {
		const showKey = privateKeyInput.type === 'password';
		privateKeyInput.type = showKey ? 'text' : 'password';
		togglePrivateButton.setAttribute('aria-pressed', showKey ? 'true' : 'false');
		togglePrivateButton.setAttribute('aria-label', showKey ? togglePrivateButton.dataset.hideLabel : togglePrivateButton.dataset.showLabel);
		togglePrivateButton.querySelector('.icon').className = showKey ? 'icon fa-eye-slash fa-fw' : 'icon fa-eye fa-fw';
	});

	const generateAndDisplayKeys = async() => {
		const buttonIcon = generateButton.querySelector('.icon');
		generateButton.disabled = true;
		generateButton.setAttribute('aria-busy', 'true');
		buttonIcon.classList.add('fa-spin');
		generationStatus.className = 'vapid-generation-status';
		generationStatus.textContent = '';

		try {
			const keyPair = await phpbb.generateVAPIDKeys();
			publicKeyInput.value = keyPair.publicKey;
			privateKeyInput.value = keyPair.privateKey;
			hidePrivateKey();

			statusBadge.className = 'vapid-status-badge is-pending';
			statusBadge.textContent = generateButton.dataset.generatedLabel;
			generationStatus.className = 'vapid-generation-status has-message';
			generationStatus.textContent = generateButton.dataset.generatedMessage;
		} catch (error) {
			console.error('Error generating VAPID keys with SubtleCrypto:', error);
			generationStatus.className = 'vapid-generation-status has-message error';
			generationStatus.textContent = generateButton.dataset.errorMessage;
		} finally {
			generateButton.disabled = false;
			generateButton.removeAttribute('aria-busy');
			buttonIcon.classList.remove('fa-spin');
		}
	};

	generateButton.addEventListener('click', () => {
		if (credentials.dataset.keysConfigured === 'true') {
			confirmationDialog.classList.add('vapid-confirm-dialog');
			confirmationDialog.setAttribute('role', 'dialog');
			confirmationDialog.setAttribute('aria-modal', 'true');
			confirmationDialog.setAttribute('aria-labelledby', 'vapid-confirm-title');

			phpbb.confirm(confirmationTemplate.innerHTML, (confirmed) => {
				if (confirmed) {
					void generateAndDisplayKeys();
				}
			});

			confirmationDialog.querySelector('input[name="cancel"]').focus();
			return;
		}

		void generateAndDisplayKeys();
	});

	settingsForm.addEventListener('reset', () => {
		window.setTimeout(() => {
			hidePrivateKey();
			statusBadge.className = initialBadgeClass;
			statusBadge.textContent = initialBadgeText;
			generationStatus.className = 'vapid-generation-status';
			generationStatus.textContent = '';
		}, 0);
	});
});
