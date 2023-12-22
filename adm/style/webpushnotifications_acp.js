/* global phpbb */

'use strict';

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
	})
})
