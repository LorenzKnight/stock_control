<div id="pricing-container">
	<div class="container">
		<div class="title">
			<h2 class="pricing-title"><?= htmlspecialchars(tr('pricing_title_main')) ?></h2>
		</div>
		<div class="subtitle">
			<p><?= htmlspecialchars(tr('pricing_subtitle')) ?></p>
		</div>
	</div>

	<div class="title">
		<p class="pricing-title"><?= htmlspecialchars(tr('pricing_employees_title')) ?></p>
	</div>
	<div class="opcions-packages">
		<div class="packs-selection">
			<input type="radio" id="group-pack-1" name="group-pack" value="0" checked>
			<label for="group-pack-1">0 - 10</label>
		</div>
		<div class="packs-selection">
			<input type="radio" id="group-pack-2" name="group-pack" value="15">
			<label for="group-pack-2">20+</label>
		</div>
		<!-- <div class="packs-selection">
			<input type="radio" id="group-pack-3" name="group-pack" value="35">
			<label for="group-pack-3">35 - 50</label>
		</div> -->
	</div>

	<div class="pricing-container">
		<!-- Aquí se insertan los paquetes dinámicamente -->
	</div>
</div>