document.addEventListener('DOMContentLoaded', () => {
	'use strict';

	const HEX_REGEX = /^#([A-Fa-f0-9]{6})$/;
	const DETECTION_TIMEOUT = 10000;

	const colorPickers = document.querySelectorAll('input[type="color"]');

	colorPickers.forEach(colorPicker => {
		const colorText = colorPicker.previousElementSibling;

		if (!colorText || colorText.type !== 'text') {
			return;
		}

		const syncColors = (source, target) => {
			const value = source.value.trim();
			target.value = HEX_REGEX.test(value) ? value : colorText.placeholder;
		};

		const handleInput = ({ target }) => {
			if (target === colorPicker) {
				colorText.value = target.value;
			} else {
				syncColors(colorText, colorPicker);
			}
		};

		colorPicker.addEventListener('input', handleInput);
		colorText.addEventListener('input', handleInput);
		colorText.addEventListener('blur', () => {
			if (!colorText.value.trim()) {
				colorPicker.value = colorText.placeholder;
			}
		});

		syncColors(colorText, colorPicker);
	});

	const readColor = (view, element) => {
		const value = view.getComputedStyle(element).backgroundColor;
		const canvas = document.createElement('canvas');
		const context = canvas.getContext('2d');

		canvas.width = 1;
		canvas.height = 1;
		context.clearRect(0, 0, 1, 1);
		context.fillStyle = value;
		context.fillRect(0, 0, 1, 1);

		return Array.from(context.getImageData(0, 0, 1, 1).data);
	};

	const compositeColor = (foreground, background) => {
		const alpha = foreground[3] / 255;

		return [
			Math.round(foreground[0] * alpha + background[0] * (1 - alpha)),
			Math.round(foreground[1] * alpha + background[1] * (1 - alpha)),
			Math.round(foreground[2] * alpha + background[2] * (1 - alpha)),
			255,
		];
	};

	const detectPageColor = iframe => {
		const previewDocument = iframe.contentDocument;
		const previewWindow = iframe.contentWindow;
		const htmlColor = readColor(previewWindow, previewDocument.documentElement);
		const bodyColor = readColor(previewWindow, previewDocument.body);

		if (htmlColor[3] === 0 && bodyColor[3] === 0) {
			throw new Error('Style has no HTML or body background colour.');
		}

		const canvasColor = compositeColor(htmlColor, [ 255, 255, 255, 255 ]);
		return compositeColor(bodyColor, canvasColor);
	};

	const toHex = color => '#' + color.slice(0, 3)
		.map(channel => channel.toString(16).padStart(2, '0'))
		.join('')
		.toUpperCase();

	const relativeLuminance = color => {
		const channels = color.slice(0, 3).map(channel => {
			const value = channel / 255;
			return value <= 0.04045 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
		});

		return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
	};

	const setColor = (styleId, name, value) => {
		const colorText = document.getElementById(`pwa_${name}_color_${styleId}`);

		colorText.value = value;
		colorText.dispatchEvent(new Event('input', { bubbles: true }));
	};

	document.querySelectorAll('.pwa-detect-colours').forEach(button => {
		button.addEventListener('click', () => {
			const originalLabel = button.value;
			const status = button.closest('dd').querySelector('.pwa-detect-status');
			const iframe = document.createElement('iframe');
			let finished = false;

			const finish = error => {
				if (finished) {
					return;
				}

				finished = true;
				clearTimeout(timeout);
				iframe.remove();
				button.disabled = false;
				button.value = originalLabel;
				status.textContent = error ? button.dataset.errorMessage : '';
			};

			const timeout = setTimeout(() => finish(true), DETECTION_TIMEOUT);

			button.disabled = true;
			button.value = button.dataset.detectingLabel;
			status.textContent = '';
			status.title = '';
			iframe.className = 'pwa-colour-preview';
			iframe.setAttribute('aria-hidden', 'true');
			iframe.setAttribute('sandbox', 'allow-same-origin');
			iframe.addEventListener('load', () => {
				try {
					const themeColor = detectPageColor(iframe);
					const backgroundColor = relativeLuminance(themeColor) > 0.179
						? '#FFFFFF'
						: '#000000';

					setColor(button.dataset.styleId, 'theme', toHex(themeColor));
					setColor(button.dataset.styleId, 'bg', backgroundColor);
					finish(false);
				} catch (error) {
					status.title = error.message;
					finish(true);
				}
			});
			iframe.addEventListener('error', () => finish(true));
			iframe.src = button.dataset.previewUrl;
			document.body.appendChild(iframe);
		});
	});
});
