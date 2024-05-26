'use strict';

function webpushWorkerUpdate() {
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register(serviceWorkerUrl)
			.then((registration) => {
				registration.update();
			})
			.catch(error => {
				// Service worker could not be updated
				console.info(error);
			});
	}
}
// Do not redeclare function if exist
if (typeof domReady === 'undefined') {
	window.domReady = function(callBack) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callBack);
		} else {
			callBack();
		}
	}
}

domReady(() => {
	/* global serviceWorkerUrl */
	webpushWorkerUpdate();
});
