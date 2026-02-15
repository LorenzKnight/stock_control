document.addEventListener("DOMContentLoaded", async function () {
    async function showBanner(bannerEl) {
		if (!bannerEl) return;
		bannerEl.classList.remove('hide');
		bannerEl.style.display = 'block';
		void bannerEl.offsetWidth;
		requestAnimationFrame(() => bannerEl.classList.add('show'));
	}
    window.showBanner = showBanner;

	async function hideBanner(bannerEl, onDone) {
		if (!bannerEl) return;

		bannerEl.classList.remove('show');
		void bannerEl.offsetWidth;
		bannerEl.classList.add('hide');

		const onEnd = (e) => {
			if (e.animationName !== 'slideUpOver') return;
			bannerEl.style.display = 'none';
			bannerEl.classList.remove('hide');
			bannerEl.removeEventListener('animationend', onEnd);
			if (typeof onDone === 'function') onDone();
		};

		bannerEl.addEventListener('animationend', onEnd);
	}
    window.hideBanner = hideBanner;
});