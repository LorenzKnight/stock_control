document.addEventListener("DOMContentLoaded", () => {
	const currentLang = window.APP_LANG || "en";

	function toPrettyUrl(urlString) {
		const url = new URL(urlString, window.location.origin);

		// Soporta legacy: ?page=gdpr (en cualquier ruta)
		const page = url.searchParams.get("page");
		if (!page) return null;

		const slug = String(page).trim().replace(/^\/+|\/+$/g, "");
		if (!slug) return null;

		return `/${encodeURIComponent(currentLang)}/${encodeURIComponent(slug)}`;
	}

	// 1) Si el usuario cayó en una URL con ?page=..., redirigir al formato nuevo
	const redirectNow = toPrettyUrl(window.location.href);
	if (redirectNow) {
		// Limpia el historial y elimina el ?page=
		window.location.replace(redirectNow);
		return;
	}

	// 2) Interceptar clicks SOLO para links con ?page=...
	document.addEventListener("click", (e) => {
		const a = e.target.closest("a");
		if (!a) return;

		const href = a.getAttribute("href");
		if (!href) return;

		// No tocar externos / new tab / modifiers
		if (href.startsWith("http") || href.startsWith("//") || a.target === "_blank") return;
		if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

		const pretty = toPrettyUrl(href);
		if (!pretty) return;

		e.preventDefault();
		window.location.href = pretty;
	});
});