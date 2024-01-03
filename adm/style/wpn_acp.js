/* global phpbb */

'use strict';

/**
 * Convert raw key ArrayBuffer to base64 string.
 *
 * @param {ArrayBuffer} rawKey Raw key array buffer as exported by SubtleCrypto exportKey()
 * @returns {string} Base64 encoded raw key string
 */
phpbb.rawKeyToBase64 = (rawKey) => {
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
	return base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
};

/**
 * This callback generates the VAPID keys for the web push notification service.
 */
phpbb.addAjaxCallback('generate_vapid_keys', () => {

	/**
	 * Generate VAPID keypair with public and private key string
	 *
	 * @returns {Promise<{privateKey: string, publicKey: string}|null>}
	 */
	async function generateVAPIDKeys() {
		try {
			// Generate a new key pair using the Subtle Crypto API
			const keyPair = await crypto.subtle.generateKey(
				{
					name: 'ECDH',
					namedCurve: 'P-256',
				},
				true,
				['deriveKey', 'deriveBits']
			);

			const privateKeyJwk = await crypto.subtle.exportKey('jwk', keyPair.privateKey);
			const privateKeyString = privateKeyJwk.d;

			const publicKeyBuffer = await crypto.subtle.exportKey('raw', keyPair.publicKey);
			const publicKeyString = phpbb.base64UrlEncode(phpbb.rawKeyToBase64(publicKeyBuffer));

			return {
				privateKey: privateKeyString,
				publicKey: publicKeyString
			};
		} catch (error) {
			console.error('Error generating keys with SubtleCrypto:', error);
			return null;
		}
	}

	generateVAPIDKeys().then(keyPair => {
		if (!keyPair) {
			return;
		}
		const publicKeyInput = document.querySelector('#webpush_vapid_public');
		const privateKeyInput = document.querySelector('#webpush_vapid_private');
		publicKeyInput.value = keyPair.publicKey;
		privateKeyInput.value = keyPair.privateKey;
	});
});
