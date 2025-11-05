document.addEventListener("DOMContentLoaded", async function () {
	const labelContainer = document.getElementById("shippingLabel");
	if (labelContainer) {
		const urlParams = new URLSearchParams(window.location.search);
		const shippingId = urlParams.get("shipping_id");

		if (!shippingId) {
			labelContainer.innerHTML = "<p style='color:red;'>Missing shipping_id parameter.</p>";
			return;
		}

		try {
			const response = await fetch(`api/get_shipping_label.php?shipping_id=${shippingId}`);
			const result = await response.json();

			if (!result.success) {
				labelContainer.innerHTML = `<p style='color:red;'>${result.message}</p>`;
				return;
			}

			const { shipping, loads } = result.data;
			const date = shipping.created_at ? shipping.created_at.substring(0, 10) : "N/A";
			const destination = shipping.destination || "N/A";
			const shippingImg = shipping.shipping_img
				? `../images/shippings-code/${shipping.shipping_img}`
				: "../images/sys-img/no-qr.png";

			// Renderizar contenido dinámico
			labelContainer.innerHTML = `
				<div class="header">AllStockControl Shipping</div>
				<div class="qr">
					<img src="${shippingImg}" alt="QR Code">
				</div>
				<div class="info">
					<strong>Shipping No:</strong> ${shipping.shipping_no}<br>
					<strong>Destination:</strong> ${destination}<br>
					<strong>Date:</strong> ${date}<br>
				</div>
				${loads && loads.length > 0 ? `
				<div class="loads">
					<strong>Loads:</strong><br>
					${loads.map(load => `• ${load.load_no} — ${load.destination}`).join("<br>")}
				</div>` : ""}
				<div class="prom">powered by: www.allstockcontrol.com</div>
			`;

			// Imprimir automáticamente al cargar
			window.print();

		} catch (err) {
			labelContainer.innerHTML = `<p style='color:red;'>Error loading data: ${err.message}</p>`;
		}
	}
});