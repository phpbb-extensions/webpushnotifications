(function() {
	'use strict';

	const dismissedKey = 'phpbb_wpn_pwa_banner_dismissed';
	let deferredPrompt = null;

	function isStandalone() {
		return ((window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true);
	}

	function isIOS() {
		return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
	}

	function isSafari() {
		const userAgent = window.navigator.userAgent;

		return (/safari/i.test(userAgent) && !/crios|fxios|edgios|opr\//i.test(userAgent));
	}

	function localStorageGet(key) {
		try {
			return window.localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function localStorageSet(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch (e) {
			// Private browsing may block localStorage.
		}
	}

	function hideInstallBanner(banner) {
		if (banner) {
			banner.hidden = true;
		}
	}

	function showIOSInstallInstructions(banner, installButton) {
		const defaultSubtitle = document.getElementById('pwa-install-subtitle-default');
		const iosSubtitle = document.getElementById('pwa-install-subtitle-ios');

		banner.hidden = false;

		if (defaultSubtitle) {
			defaultSubtitle.hidden = true;
		}

		if (iosSubtitle) {
			iosSubtitle.hidden = false;
		}

		if (installButton) {
			installButton.hidden = true;
		}
	}

	function setupInstallBanner() {
		const banner = document.getElementById('pwa-install-banner');
		const installButton = document.getElementById('pwa-btn-install');
		const dismissButton = document.getElementById('pwa-btn-dismiss');

		if (!banner || !dismissButton || isStandalone()) {
			hideInstallBanner(banner);
			return;
		}

		if (localStorageGet(dismissedKey) === '1') {
			hideInstallBanner(banner);
			return;
		}

		// iOS Safari does not support beforeinstallprompt.
		if (isIOS() && isSafari()) {
			showIOSInstallInstructions(banner, installButton);

			dismissButton.addEventListener('click', () => {
				hideInstallBanner(banner);
				localStorageSet(dismissedKey, '1');
			});

			return;
		}

		// Chromium install flow.
		window.addEventListener('beforeinstallprompt', event => {
			event.preventDefault();
			deferredPrompt = event;
			banner.hidden = false;
		});

		if (installButton) {
			installButton.addEventListener('click', () => {
				if (!deferredPrompt) {
					hideInstallBanner(banner);
					return;
				}

				deferredPrompt.prompt();
				deferredPrompt.userChoice.then(choice => {
					if (choice.outcome === 'accepted') {
						hideInstallBanner(banner);
					}

					deferredPrompt = null;
				});
			});
		}

		dismissButton.addEventListener('click', () => {
			hideInstallBanner(banner);
			localStorageSet(dismissedKey, '1');
		});

		window.addEventListener('appinstalled', () => {
			hideInstallBanner(banner);
			localStorageSet(dismissedKey, '1');
			deferredPrompt = null;
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupInstallBanner);
	} else {
		setupInstallBanner();
	}
})();
