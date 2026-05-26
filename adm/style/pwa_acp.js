document.addEventListener('DOMContentLoaded', () => {
	const DEFAULT_COLOR = '#000000';
	const HEX_REGEX = /^#([A-Fa-f0-9]{6})$/;

	const colorPickers = document.querySelectorAll('input[type="color"]');

	colorPickers.forEach(colorPicker => {
		const colorText = colorPicker.previousElementSibling;

		if (!colorText || colorText.type !== 'text') {
			return;
		}

		const syncColors = (source, target) => {
			const value = source.value.trim();
			target.value = HEX_REGEX.test(value) ? value : DEFAULT_COLOR;
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
				colorPicker.value = DEFAULT_COLOR;
			}
		});

		syncColors(colorText, colorPicker);
	});
});
