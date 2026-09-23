window.loadAiSales = async function () {
	const companyContainer =
		document.getElementById('ai-sales-company-list');

	const searchCompanyField =
		document.getElementById('searchSalesCompanyField');

	const companyDetails =
		document.getElementById('ai-sales-details');

	if (!companyContainer) return;


	// Evita insertar directamente texto externo sin escapar
	function escapeHtml(value) {
		return String(value ?? '')
			.replaceAll('&', '&amp;')
			.replaceAll('<', '&lt;')
			.replaceAll('>', '&gt;')
			.replaceAll('"', '&quot;')
			.replaceAll("'", '&#039;');
	}


	function renderCompanyDetails(company) {
		if (!companyDetails) return;

		companyDetails.innerHTML = `
			<div style="padding: 20px;">

				<h2>
					${escapeHtml(company.company_name)}
				</h2>

				<p>
					<strong>Market:</strong>
					${escapeHtml(company.market || '-')}
				</p>

				<p>
					<strong>Country:</strong>
					${escapeHtml(company.country || '-')}
				</p>

				<p>
					<strong>City:</strong>
					${escapeHtml(company.city || '-')}
				</p>

				<p>
					<strong>Industry:</strong>
					${escapeHtml(company.industry || '-')}
				</p>

				<p>
					<strong>Language:</strong>
					${escapeHtml(company.language || '-')}
				</p>

				<p>
					<strong>Website:</strong>
					${escapeHtml(company.website || '-')}
				</p>

				<p>
					<strong>Source:</strong>
					${escapeHtml(company.source || '-')}
				</p>

				<p>
					<strong>Description:</strong>
				</p>

				<p>
					${escapeHtml(company.description || 'No description available.')}
				</p>

				<div id="ai-sales-contacts"></div>

				<div id="ai-sales-leads"></div>

			</div>
		`;

		loadSalesContacts(
			company.sales_company_id
		);

		loadSalesLeads(
			company.sales_company_id
		);
	}


	async function loadSalesLeads(
		salesCompanyId
	) {
		const leadsContainer = document.getElementById('ai-sales-leads');

		if (!leadsContainer) {
			return;
		}

		leadsContainer.innerHTML = `
			<p>Loading sales opportunity...</p>
		`;

		try {
			const response = await fetch(
				`api/get_sales_leads.php?sales_company_id=${salesCompanyId}`,
				{
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}
			);

			const data = await response.json();

			if (
				!data.success ||
				!Array.isArray(data.data) ||
				data.data.length === 0
			) {
				leadsContainer.innerHTML = `
					<hr>

					<h3>Sales Opportunity</h3>

					<p>No sales opportunity found.</p>
				`;

				return;
			}

			leadsContainer.innerHTML = `
				<hr>

				<h3>Sales Opportunity</h3>

				${data.data.map(lead => `
					<div class="ai-sales-lead">

						<p>
							<strong>Stage:</strong>
							${escapeHtml(lead.stage || 'NEW')}
						</p>

						<p>
							<strong>Score:</strong>
							${Number(lead.score ?? 0)}
						</p>

						<p>
							<strong>Source:</strong>
							${escapeHtml(lead.source || '-')}
						</p>

						<p>
							<strong>Next action:</strong>
							${escapeHtml(lead.next_action || '-')}
						</p>

						<p>
							<strong>Last contact:</strong>
							${escapeHtml(lead.last_contact_at || '-')}
						</p>

						${
							lead.score_reason
								? `
									<p>
										<strong>
											Score reason:
										</strong>

										${escapeHtml(lead.score_reason)}
									</p>
								`
								: ''
						}

						${
							lead.ai_summary
								? `
									<p>
										<strong>
											AI Summary:
										</strong>

										${escapeHtml(lead.ai_summary)}
									</p>
								`
								: ''
						}

					</div>
				`).join('')}
			`;

		} catch (error) {
			console.error('Error loading sales leads:', error);

			leadsContainer.innerHTML = `
				<hr>

				<h3>Sales Opportunity</h3>

				<p style="color:red;">
					Error loading sales opportunity.
				</p>
			`;
		}
	}


	async function loadSalesContacts(
		salesCompanyId
	) {
		const contactsContainer = document.getElementById('ai-sales-contacts');

		if (!contactsContainer) {
			return;
		}

		contactsContainer.innerHTML = `
			<p>
				Loading contacts...
			</p>
		`;

		try {
			const response = await fetch(
				`api/get_sales_contacts.php?sales_company_id=${salesCompanyId}`,
				{
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}
			);

			const data = await response.json();


			if (
				!data.success ||
				!Array.isArray(data.data) ||
				data.data.length === 0
			) {
				contactsContainer.innerHTML = `
					<hr>

					<h3>
						Contacts
					</h3>

					<p>
						No contacts found.
					</p>
				`;

				return;
			}


			contactsContainer.innerHTML = `
				<hr>

				<h3>Contacts</h3>

				${data.data.map(contact => `
					<div class="ai-sales-contact">
						<strong>
							${escapeHtml(contact.full_name || 'Unknown contact')}
						</strong>

						<p>
							${escapeHtml(contact.job_title || '-')}
						</p>

						<p>
							${escapeHtml(contact.email || '-')}
						</p>

						<p>
							${escapeHtml(contact.phone || '-')}
						</p>

						${contact.is_primary ? `<small>Primary contact</small>` : ''}
					</div>
				`).join('')}
			`;

		} catch (error) {
			console.error('Error loading sales contacts:', error);

			contactsContainer.innerHTML = `
				<hr>

				<h3>Contacts</h3>

				<p style="color:red;">
					Error loading contacts.
				</p>
			`;
		}
	}


	async function fetchAndRenderCompanies() {
		try {
			const searchTerm =
				searchCompanyField?.value.trim() || '';

			const params = new URLSearchParams();

			if (searchTerm) {
				params.append(
					'search',
					searchTerm
				);
			}

			const response = await fetch(
				`api/get_sales_companies.php?${params.toString()}`,
				{
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}
			);

			const data = await response.json();

			companyContainer.innerHTML = '';

			if (
				data.success &&
				Array.isArray(data.data) &&
				data.data.length > 0
			) {
				data.data.forEach(company => {

					const row =
						document.createElement('div');

					row.className =
						'sys-admin-table ai-sales-company-row';

					row.dataset.companyId =
						String(company.sales_company_id);

					row.innerHTML = `
						<table
							width="100%"
							cellspacing="0"
							cellpadding="0"
						>
							<tr>
								<td
									width="55%"
									style="padding-left: 15px;"
								>
									<strong>
										${escapeHtml(company.company_name)}
									</strong>

									<p class="mini-title">
										${escapeHtml(company.industry || 'Unknown industry')}
									</p>
								</td>

								<td width="25%">
									${escapeHtml(company.country || '-')}
								</td>

								<td
									width="20%"
									align="center"
								>
									${escapeHtml(company.market || '-')}
								</td>
							</tr>
						</table>
					`;

					row.addEventListener(
						'click',
						() => {
							document
								.querySelectorAll(
									'.ai-sales-company-row'
								)
								.forEach(item => {
									item.classList.remove(
										'selected-company'
									);
								});

							row.classList.add(
								'selected-company'
							);

							renderCompanyDetails(
								company
							);
						}
					);

					companyContainer.appendChild(
						row
					);
				});

			} else {
				companyContainer.innerHTML = `
					<p style="
						text-align: center;
						padding: 20px;
					">
						No sales companies found.
					</p>
				`;
			}

		} catch (error) {
			console.error(
				'Error loading AI Sales companies:',
				error
			);

			companyContainer.innerHTML = `
				<p style="
					text-align:center;
					color:red;
					padding:20px;
				">
					Error loading sales companies.
				</p>
			`;
		}
	}


	searchCompanyField?.addEventListener(
		'keyup',
		fetchAndRenderCompanies
	);

	await fetchAndRenderCompanies();
};