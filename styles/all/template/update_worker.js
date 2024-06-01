'use strict';

/* global phpbbWebpushOptions, domReady */
function webpushWorkerUpdate() {
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.getRegistration(phpbbWebpushOptions.serviceWorkerUrl)
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
	};
}

domReady(() => {
	webpushWorkerUpdate();
});
