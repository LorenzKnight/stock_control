<section class="excel-section" id="why-not-excel">
	<div class="excel-section-container">
		<div class="excel-copy">
			<div class="excel-eyebrow">
				<?= htmlspecialchars(tr('excel_eyebrow') ?? 'Simpler inventory management') ?>
			</div>

			<h2>
				<?= htmlspecialchars(tr('excel_h2')) ?>
			</h2>

			<div class="excel-title-line"></div>

			<p>
				<?= htmlspecialchars(tr('excel_p1')) ?>
			</p>

			<p>
				<?= htmlspecialchars(tr('excel_p2')) ?>
			</p>

			<button class="excel-main-cta button-style-agree toggle-link">
				<span>
					<?= htmlspecialchars(tr('excel_cta')) ?>
				</span>

				<span class="excel-cta-arrow" aria-hidden="true">
					→
				</span>
			</button>

			<div class="excel-cta-notes">
				<span>
					✓ <?= htmlspecialchars(tr('cta_no_card') ?? 'No credit card required') ?>
				</span>

				<span>
					✓ <?= htmlspecialchars(tr('cta_cancel_anytime') ?? 'Cancel anytime') ?>
				</span>
			</div>
		</div>

		<div class="excel-comparison">

			<div class="excel-comparison-column excel-before">
				<div class="excel-comparison-header">
					<div class="excel-comparison-icon excel-icon-before">
						X
					</div>

					<strong>
						<?= htmlspecialchars(tr('excel_before_title') ?? 'With Excel') ?>
					</strong>
				</div>

				<div class="excel-comparison-item is-negative">
					<span class="excel-status-icon">×</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_problem_1_title') ?? 'Outdated stock levels') ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_problem_1_desc') ?? 'Information can quickly become outdated.') ?>
						</p>
					</div>
				</div>

				<div class="excel-comparison-item is-negative">
					<span class="excel-status-icon">×</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_problem_2_title') ?? 'Manual mistakes') ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_problem_2_desc') ?? 'Manual handling increases the risk of errors.') ?>
						</p>
					</div>
				</div>

				<div class="excel-comparison-item is-negative">
					<span class="excel-status-icon">×</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_problem_3_title') ?? 'Hard to scale') ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_problem_3_desc') ?? 'Spreadsheets become harder to manage as the business grows.') ?>
						</p>
					</div>
				</div>
			</div>

			<div class="excel-transition-arrow" aria-hidden="true">
				→
			</div>

			<div class="excel-comparison-column excel-after">
				<div class="excel-comparison-header">
					<div class="excel-comparison-icon excel-icon-after">
						▣
					</div>

					<strong>
						AllStockControl
					</strong>
				</div>

				<div class="excel-comparison-item is-positive">
					<span class="excel-status-icon">✓</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_li_1')) ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_solution_1_desc') ?? 'Always work with updated inventory information.') ?>
						</p>
					</div>
				</div>

				<div class="excel-comparison-item is-positive">
					<span class="excel-status-icon">✓</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_li_2')) ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_solution_2_desc') ?? 'Reduce manual errors in daily inventory operations.') ?>
						</p>
					</div>
				</div>

				<div class="excel-comparison-item is-positive">
					<span class="excel-status-icon">✓</span>

					<div>
						<strong>
							<?= htmlspecialchars(tr('excel_li_3')) ?>
						</strong>

						<p>
							<?= htmlspecialchars(tr('excel_solution_3_desc') ?? 'Manage inventory across users and locations more easily.') ?>
						</p>
					</div>
				</div>
			</div>

		</div>

	</div>
</section>