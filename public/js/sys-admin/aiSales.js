window.loadAiSales = async function () {
	const companyContainer = document.getElementById('ai-sales-company-list');
	const searchCompanyField = document.getElementById('searchSalesCompanyField');

	const companyDetails = document.getElementById('ai-sales-details');
	const openProspectSearchBtn = document.getElementById('openProspectSearchBtn');

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

	const salesContactsCache = new Map();

	let prospectSearchContext = {
		market: null,
		language: null
	};

	async function getSalesContacts(salesCompanyId) {
		const companyId = Number(salesCompanyId);

		if (!salesContactsCache.has(companyId)) {
			const request = (async () => {
				const response = await fetch(
					`api/get_sales_contacts.php?sales_company_id=${companyId}`,
					{
						method: 'GET',
						headers: {
							'Accept': 'application/json'
						}
					}
				);

				if (!response.ok) {
					throw new Error(
						'Could not load sales contacts.'
					);
				}

				const data = await response.json();

				if (
					!data.success ||
					!Array.isArray(data.data)
				) {
					throw new Error(
						data.message ||
						'Invalid sales contacts response.'
					);
				}

				return data.data;
			})();

			salesContactsCache.set(
				companyId,
				request
			);
		}

		try {
			return await salesContactsCache.get(companyId);

		} catch (error) {
			salesContactsCache.delete(companyId);
			throw error;
		}
	}

	function getBubbleClass(value) {
		const status =
			String(value ?? '')
				.trim()
				.toUpperCase();

		switch (status) {

			// Success
			case 'SENT':
			case 'DELIVERED':
			case 'RECEIVED':
			case 'INTERESTED':
			case 'WON':
			case 'INBOUND':
				return 'bubble bubble-success';


			// Warning / waiting
			case 'WAITING_REPLY':
			case 'PENDING_APPROVAL':
			case 'RESEARCHING':
			case 'NEGOTIATION':
				return 'bubble bubble-warning';


			// Error / negative
			case 'FAILED':
			case 'LOST':
				return 'bubble bubble-danger';


			// Active / information
			case 'CONTACTED':
			case 'APPROVED':
			case 'OPEN':
			case 'REPLIED':
			case 'DEMO':
			case 'EMAIL':
			case 'OUTBOUND':
				return 'bubble bubble-info';


			// Neutral
			case 'NEW':
			case 'DRAFT':
			case 'CLOSED':
			default:
				return 'bubble bubble-neutral';
		}
	}


	function renderProspectSearch() {
		if (!companyDetails) {
			return;
		}


		companyDetails.innerHTML = `
			<div class="ai-sales-prospecting">
				<div class="ai-sales-prospecting-header">
					<div>
						<h2>Find prospects</h2>

						<p>
							Search for companies that could benefit
							from AllStockControl.
						</p>
					</div>

					<div class="bubble bubble-info">
						AI Prospecting
					</div>
				</div>


				<form
					id="ai-sales-prospect-form"
					class="ai-sales-prospect-form"
				>
					<div class="ai-sales-prospect-grid">

						<div class="ai-sales-prospect-field">
							<label for="prospectMarket">
								Market
							</label>

							<select
								id="prospectMarket"
								class="form-input-style"
							>
								<option value="LATAM">
									LATAM
								</option>

								<option value="SWEDEN">
									SWEDEN
								</option>

								<option value="OTHER">
									OTHER
								</option>
							</select>
						</div>


						<div class="ai-sales-prospect-field">
							<label for="prospectCountry">
								Country
							</label>

							<input
								type="text"
								id="prospectCountry"
								class="form-input-style"
								placeholder="Dominican Republic"
								required
							>
						</div>


						<div class="ai-sales-prospect-field">
							<label for="prospectCity">
								City
							</label>

							<input
								type="text"
								id="prospectCity"
								class="form-input-style"
								placeholder="Santo Domingo"
							>
						</div>


						<div class="ai-sales-prospect-field">
							<label for="prospectIndustry">
								Industry
							</label>

							<input
								type="text"
								id="prospectIndustry"
								class="form-input-style"
								placeholder="Logistics"
								required
							>
						</div>


						<div class="ai-sales-prospect-field">
							<label for="prospectLanguage">
								Language
							</label>

							<select class="form-input-style" id="prospectLanguage">
								<option value="">
									Automatic
								</option>

								<option value="en">
									English
								</option>

								<option value="es">
									Spanish
								</option>

								<option value="sv">
									Swedish
								</option>
							</select>
						</div>


						<div class="ai-sales-prospect-field">
							<label for="prospectLimit">
								Results
							</label>

							<select class="form-input-style" id="prospectLimit">
								<option value="5">
									5
								</option>

								<option value="10">
									10
								</option>

								<option value="15">
									15
								</option>

								<option value="20">
									20
								</option>
							</select>
						</div>

					</div>


					<div class="ai-sales-prospect-suggestion">
						<h3>
							Optional company suggestion
						</h3>

						<p>
							Suggest a company that should also be
							checked independently.
						</p>


						<div class="ai-sales-prospect-grid">

							<div class="ai-sales-prospect-field">
								<label for="prospectSuggestionName">
									Company name
								</label>

								<input
									type="text"
									id="prospectSuggestionName"
									class="form-input-style"
									placeholder="DSV"
								>
							</div>


							<div class="ai-sales-prospect-field">
								<label for="prospectSuggestionWebsite">
									Website
								</label>

								<input
									type="text"
									id="prospectSuggestionWebsite"
									class="form-input-style"
									placeholder="https://www.dsv.com"
								>
							</div>


							<div class="ai-sales-prospect-field">
								<label for="prospectSuggestionCountry">
									Country
								</label>

								<input
									type="text"
									id="prospectSuggestionCountry"
									class="form-input-style"
									placeholder="Dominican Republic"
								>
							</div>

						</div>
					</div>


					<div class="ai-sales-prospect-actions">
						<button
							type="submit"
							id="searchProspectsBtn"
							class="button-style-agree"
						>
							Search prospects
						</button>
					</div>
				</form>


				<div
					id="ai-sales-prospect-results"
					class="ai-sales-prospect-results"
				>
				</div>

			</div>
		`;


		const form =
			document.getElementById(
				'ai-sales-prospect-form'
			);

		form?.addEventListener(
			'submit',
			searchProspects
		);
	}


	async function searchProspects(
		event
	) {
		event.preventDefault();


		const resultsContainer =
			document.getElementById(
				'ai-sales-prospect-results'
			);

		const button =
			document.getElementById(
				'searchProspectsBtn'
			);

		if (
			!resultsContainer ||
			!button
		) {
			return;
		}


		const market =
			document.getElementById(
				'prospectMarket'
			)?.value.trim() || '';

		const country =
			document.getElementById(
				'prospectCountry'
			)?.value.trim() || '';

		const city =
			document.getElementById(
				'prospectCity'
			)?.value.trim() || '';

		const industry =
			document.getElementById(
				'prospectIndustry'
			)?.value.trim() || '';

		const language =
			document.getElementById(
				'prospectLanguage'
			)?.value || '';

		const limit =
			Number(
				document.getElementById(
					'prospectLimit'
				)?.value || 5
			);


		const suggestionName =
			document.getElementById(
				'prospectSuggestionName'
			)?.value.trim() || '';

		const suggestionWebsite =
			document.getElementById(
				'prospectSuggestionWebsite'
			)?.value.trim() || '';

		const suggestionCountry =
			document.getElementById(
				'prospectSuggestionCountry'
			)?.value.trim() || '';


		if (
			country === '' ||
			industry === ''
		) {
			alert(
				'Country and industry are required.'
			);

			return;
		}


		const search = {
			search_id:
				`ui-${Date.now()}`,

			market:
				market || null,

			country:
				country,

			city:
				city || null,

			industry:
				industry,

			language:
				language || null,

			limit:
				limit
		};


		const suggestions = [];

		if (suggestionName !== '') {
			suggestions.push(
				{
					name:
						suggestionName,

					website:
						suggestionWebsite || null,

					country:
						suggestionCountry || null
				}
			);
		}


		const originalText =
			button.textContent;

		button.disabled = true;
		button.textContent =
			'Searching...';


		resultsContainer.innerHTML = `
			<div class="ai-sales-prospect-loading">
				<div class="ai-sales-empty-loading-icon">
					<img
						src="images/sys-img/loading2.gif"
						alt="AI Sales"
						width="100"
						height="100"
					>
				</div>

				<div>
					<p>
						AI is searching and analyzing companies...
					</p>
				</div>
			</div>
		`;

		// TEMPORARY:
		// Keep loading state visible while redesigning.
		// return;

		prospectSearchContext = {
			market:
				market || null,

			language:
				language || null
		};


		try {
			const response =
				await fetch(
					'api/discover_sales_companies.php',
					{
						method: 'POST',

						headers: {
							'Accept':
								'application/json',

							'Content-Type':
								'application/json'
						},

						body:
							JSON.stringify(
								{
									searches: [
										search
									],

									suggestions:
										suggestions
								}
							)
					}
				);


			const data =
				await response.json();


			if (!data.success) {
				throw new Error(
					data.message ||
					'Could not discover companies.'
				);
			}


			const companies =
				data.data?.companies;


			if (!Array.isArray(companies)) {
				throw new Error(
					'Invalid company discovery response.'
				);
			}


			renderProspectResults(
				companies
			);

		} catch (error) {
			console.error(
				'Error discovering companies:',
				error
			);

			resultsContainer.innerHTML = `
				<div class="ai-sales-prospect-error">
					<strong>
						Could not search prospects.
					</strong>

					<p>
						${escapeHtml(
							error.message ||
							'Unknown error.'
						)}
					</p>
				</div>
			`;

		} finally {
			button.disabled = false;
			button.textContent =
				originalText;
		}
	}


	function renderProspectResults(
		companies
	) {
		const resultsContainer =
			document.getElementById(
				'ai-sales-prospect-results'
			);

		if (!resultsContainer) {
			return;
		}


		if (companies.length === 0) {
			resultsContainer.innerHTML = `
				<div class="ai-sales-prospect-empty">
					<h3>No prospects found</h3>

					<p>
						Try changing the country,
						city or industry.
					</p>
				</div>
			`;

			return;
		}


		resultsContainer.innerHTML = `
			<div class="ai-sales-prospect-results-header">
				<div>
					<h3>
						Prospects found
					</h3>

					<p>
						${companies.length}
						companies found.
					</p>
				</div>

				<div class="bubble bubble-success">
					${companies.length} results
				</div>
			</div>


			<div class="ai-sales-prospect-list">

				${companies.map(
					(company, index) => `
						<div class="ai-sales-prospect-card">

							<div class="ai-sales-prospect-card-header">

								<div>
									<h3>
										${escapeHtml(
											company.name ||
											'Unknown company'
										)}
									</h3>

									<div class="ai-sales-company-meta">

										${
											company.industry
												? `
													<span>
														${escapeHtml(
															company.industry
														)}
													</span>
												`
												: ''
										}

										${
											company.country
												? `
													<span>
														${escapeHtml(
															company.country
														)}
													</span>
												`
												: ''
										}

										${
											company.city
												? `
													<span>
														${escapeHtml(
															company.city
														)}
													</span>
												`
												: ''
										}

									</div>
								</div>


								<div class="ai-sales-prospect-badges">

									<div class="bubble bubble-info">
										${escapeHtml(
											company.source_type ||
											'DISCOVERY'
										)}
									</div>

									${
										company.suggested_by_user
											? `
												<div class="bubble bubble-warning">
													Suggested
												</div>
											`
											: ''
									}

								</div>

							</div>


							${
								company.source_text
									? `
										<p class="ai-sales-prospect-description">
											${escapeHtml(
												company.source_text
											)}
										</p>
									`
									: ''
							}


							<div class="ai-sales-prospect-links">

								${
									company.website
										? `
											<a
												href="${escapeHtml(
													company.website
												)}"
												target="_blank"
												rel="noopener noreferrer"
											>
												Website
											</a>
										`
										: ''
								}


								${
									company.source_url
										? `
											<a
												href="${escapeHtml(
													company.source_url
												)}"
												target="_blank"
												rel="noopener noreferrer"
											>
												Source
											</a>
										`
										: ''
								}

								<button
									type="button"
									class="button-style-agree ai-sales-add-prospect-btn"
									data-prospect-index="${index}"
								>
									Add prospect
								</button>

							</div>

						</div>
					`
				).join('')}

			</div>
		`;

		const addProspectButtons =
			resultsContainer.querySelectorAll(
				'.ai-sales-add-prospect-btn'
			);

		addProspectButtons.forEach(
			button => {
				button.addEventListener(
					'click',
					async () => {
						const index =
							Number(
								button.dataset.prospectIndex
							);

						const company =
							companies[index];

						if (!company) {
							return;
						}

						await addProspect(
							company,
							button
						);
					}
				);
			}
		);
	}


	async function findContactsProspect(
		salesCompanyId
	) {
		const response =
			await fetch(
				'api/find_sales_contacts.php',
				{
					method: 'POST',

					headers: {
						'Accept':
							'application/json',

						'Content-Type':
							'application/json'
					},

					body:
						JSON.stringify(
							{
								sales_company_id:
									salesCompanyId
							}
						)
				}
			);


		const responseText =
			await response.text();


		let data;


		try {
			data =
				JSON.parse(
					responseText
				);

		} catch (error) {
			throw new Error(
				`Server returned HTTP ${response.status} instead of JSON.`
			);
		}


		if (
			!response.ok ||
			!data.success
		) {
			throw new Error(
				data.message ||
					'Could not search company contacts.'
			);
		}


		return data.data;
	}


	async function qualifyProspect(
		salesCompanyId
	) {
		const response =
			await fetch(
				'api/qualify_sales_company.php',
				{
					method: 'POST',

					headers: {
						'Accept':
							'application/json',

						'Content-Type':
							'application/json'
					},

					body:
						JSON.stringify(
							{
								sales_company_id:
									salesCompanyId
							}
						)
				}
			);


		const responseText =
			await response.text();


		let data;


		try {
			data =
				JSON.parse(
					responseText
				);

		} catch (error) {
			throw new Error(
				`Server returned HTTP ${response.status} instead of JSON.`
			);
		}


		if (
			!response.ok ||
			!data.success
		) {
			throw new Error(
				data.message ||
					'Could not qualify company.'
			);
		}


		return data.data;
	}


	async function researchProspect(
		salesCompanyId
	) {
		const response =
			await fetch(
				'api/research_sales_company.php',
				{
					method: 'POST',

					headers: {
						'Accept':
							'application/json',

						'Content-Type':
							'application/json'
					},

					body:
						JSON.stringify(
							{
								sales_company_id:
									salesCompanyId
							}
						)
				}
			);


		const responseText =
			await response.text();


		let data;


		try {
			data =
				JSON.parse(
					responseText
				);

		} catch (error) {
			throw new Error(
				`Server returned HTTP ${response.status} instead of JSON.`
			);
		}


		if (
			!response.ok ||
			!data.success
		) {
			throw new Error(
				data.message ||
					'Could not research company.'
			);
		}


		return data.data;
	}


	function renderResearchProcessing() {
		if (!companyDetails) {
			return;
		}


		const processing =
			document.createElement(
				'div'
			);

		processing.className =
			'ai-sales-processing';

		processing.innerHTML = `
			<div class="ai-sales-empty-loading-icon">
				<img
					src="images/sys-img/loading2.gif"
					alt="Processing"
					class="ai-sales-processing-gif"
				>
			</div>

			<div>
				<strong>
					Researching company...
				</strong>

				<p>
					AI is analyzing company information and verified sources.
				</p>
			</div>
		`;


		companyDetails.prepend(
			processing
		);
	}


	async function addProspect(
		company,
		button
	) {
		const originalText =
			button.textContent;


		button.disabled = true;
		button.textContent =
			'Adding...';


		try {
			const response =
				await fetch(
					'api/create_sales_company.php',
					{
						method: 'POST',

						headers: {
							'Accept':
								'application/json',

							'Content-Type':
								'application/json'
						},

						body:
							JSON.stringify(
								{
									company:
										company,

									market:
										prospectSearchContext.market,

									language:
										prospectSearchContext.language
								}
							)
					}
				);


			const data =
				await response.json();


			if (!data.success) {
				throw new Error(
					data.message ||
						'Could not add prospect.'
				);
			}


			const created =
				Boolean(
					data.data?.created
				);

			const salesCompanyId =
				Number(
					data.data?.sales_company_id
				);


			button.textContent =
				created
					? 'Added'
					: 'Already added';

			button.disabled =
				true;


			await fetchAndRenderCompanies();


			if (
				!created ||
				salesCompanyId <= 0
			) {
				return;
			}


			button.textContent =
				'Researching...';


			renderResearchProcessing();

			try {
				await researchProspect(
					salesCompanyId
				);


				button.textContent =
					'Added';


				await fetchAndRenderCompanies();

			} catch (researchError) {
				console.error(
					'Error researching prospect:',
					researchError
				);


				button.textContent =
					'Added · Research failed';


				await fetchAndRenderCompanies();
			}

		} catch (error) {
			console.error(
				'Error adding prospect:',
				error
			);

			alert(
				error.message ||
					'Could not add prospect.'
			);

			button.disabled =
				false;

			button.textContent =
				originalText;
		}
	}

	
	function getResearchStatusBubble(
		status
	) {
		switch (
			String(
				status || ''
			).toUpperCase()
		) {
			case 'COMPLETED':
				return 'bubble bubble-success';

			case 'RESEARCHING':
				return 'bubble bubble-info';

			case 'FAILED':
				return 'bubble bubble-danger';

			case 'PENDING':
			default:
				return 'bubble bubble-warning';
		}
	}


	function parseResearchSources(
		value
	) {
		if (Array.isArray(value)) {
			return value;
		}


		if (
			typeof value !== 'string' ||
			value.trim() === ''
		) {
			return [];
		}


		try {
			const parsed =
				JSON.parse(
					value
				);

			return Array.isArray(parsed)
				? parsed
				: [];

		} catch (error) {
			console.error(
				'Could not parse research sources:',
				error
			);

			return [];
		}
	}


	function stripHtmlTags(
		value
	) {
		return String(
			value || ''
		)
			.replace(
				/<[^>]*>/g,
				''
			)
			.trim();
	}


	function renderCompanyResearch(
		company
	) {
		const status =
			String(
				company.research_status ||
				'PENDING'
			).toUpperCase();


		if (status === 'RESEARCHING') {
			return `
				<section
					class="ai-sales-card ai-sales-research-card"
					id="ai-sales-company-research"
				>
					<div class="ai-sales-header">
						<h3>
							Company Research
						</h3>

						<span class="${getResearchStatusBubble(status)}">
							RESEARCHING
						</span>
					</div>

					<div class="ai-sales-processing">
						<img
							src="images/sys-img/loading2.gif"
							alt="Processing"
							class="ai-sales-processing-gif"
						>

						<div>
							<strong>
								Researching company...
							</strong>

							<p>
								AI is analyzing company information
								and verified sources.
							</p>
						</div>
					</div>
				</section>
			`;
		}


		if (status === 'FAILED') {
			return `
				<section
					class="ai-sales-card ai-sales-research-card"
					id="ai-sales-company-research"
				>
					<div class="ai-sales-header">
						<h3>
							Company Research
						</h3>

						<span class="${getResearchStatusBubble(status)}">
							FAILED
						</span>
					</div>

					<p class="ai-sales-research-message">
						Company research could not be completed.
					</p>

					<button
						type="button"
						class="button-style-agree"
						data-research-company-id="${
							Number(
								company.sales_company_id
							)
						}"
					>
						Retry research
					</button>
				</section>
			`;
		}


		if (status === 'PENDING') {
			return `
				<section
					class="ai-sales-card ai-sales-research-card"
					id="ai-sales-company-research"
				>
					<div class="ai-sales-header">
						<h3>
							Company Research
						</h3>

						<span class="${getResearchStatusBubble(status)}">
							PENDING
						</span>
					</div>

					<p class="ai-sales-research-message">
						Waiting for company research.
					</p>

					<button
						type="button"
						class="button-style-agree"
						data-research-company-id="${
							Number(
								company.sales_company_id
							)
						}"
					>
						Start research
					</button>
				</section>
			`;
		}


		const sources =
			parseResearchSources(
				company.research_sources
			);


		return `
			<section
				class="ai-sales-card ai-sales-research-card"
				id="ai-sales-company-research"
			>
				<div class="ai-sales-header">
					<h3>
						Company Research
					</h3>

					<span class="${getResearchStatusBubble(status)}">
						COMPLETED
					</span>
				</div>

				<div class="ai-sales-research-content">

					<div class="ai-sales-research-section">
						<strong>
							Summary
						</strong>

						<p
							id="ai-sales-research-summary"
							class="ai-sales-research-summary-text"
						>
							${escapeHtml(
								company.research_summary ||
								'No research summary available.'
							)}
						</p>

						<button
							type="button"
							class="ai-sales-read-more"
							data-research-summary-toggle
							hidden
						>
							Read more
						</button>
					</div>

					<div class="ai-sales-research-section">
						<strong>
							Inventory context
						</strong>

						<p>
							${escapeHtml(
								company.research_inventory_context ||
								'No inventory-related context was confirmed.'
							)}
						</p>
					</div>

				</div>

				${
					sources.length > 0
						? `
							<div class="ai-sales-research-sources">
								<strong>
									Sources
								</strong>

								<div class="ai-sales-research-source-list">
									${sources.map(
										(source, index) => `
											<a
												href="${escapeHtml(source)}"
												target="_blank"
												rel="noopener noreferrer"
											>
												Source ${index + 1}
											</a>
										`
									).join('')}
								</div>
							</div>
						`
						: ''
				}

				${
					company.researched_at
						? `
							<p class="ai-sales-research-date">
								Researched:
								${escapeHtml(
									company.researched_at
								)}
							</p>
						`
						: ''
				}
			</section>
		`;
	}


	function getQualificationStatusBubble(
		status
	) {
		switch (
			String(
				status || ''
			).toUpperCase()
		) {
			case 'COMPLETED':
				return 'bubble bubble-success';

			case 'QUALIFYING':
				return 'bubble bubble-info';

			case 'FAILED':
				return 'bubble bubble-danger';

			case 'PENDING':
			default:
				return 'bubble bubble-warning';
		}
	}


	function getQualificationLevelBubble(
		level
	) {
		switch (
			String(
				level || ''
			).toUpperCase()
		) {
			case 'HIGH':
				return 'bubble bubble-success';

			case 'MEDIUM':
				return 'bubble bubble-warning';

			case 'LOW':
				return 'bubble bubble-danger';

			default:
				return 'bubble bubble-neutral';
		}
	}


	function renderCompanyQualification(
		company
	) {
		const status =
			String(
				company.qualification_status ||
				'PENDING'
			).toUpperCase();


		if (
			company.research_status !==
				'COMPLETED'
		) {
			return `
				<section
					class="ai-sales-card ai-sales-qualification-card"
					id="ai-sales-company-qualification"
				>
					<div class="ai-sales-header">
						<h3>
							Company Qualification
						</h3>

						<span class="bubble bubble-neutral">
							WAITING
						</span>
					</div>

					<p class="ai-sales-qualification-message">
						Company research must be completed first.
					</p>
				</section>
			`;
		}


		if (status === 'QUALIFYING') {
			return `
				<section
					class="ai-sales-card ai-sales-qualification-card"
					id="ai-sales-company-qualification"
				>
					<div class="ai-sales-header">
						<h3>
							Company Qualification
						</h3>

						<span class="${getQualificationStatusBubble(status)}">
							QUALIFYING
						</span>
					</div>

					<div class="ai-sales-processing">
						<img
							src="images/sys-img/loading2.gif"
							alt="Processing"
							class="ai-sales-processing-gif"
						>

						<div>
							<strong>
								Qualifying company...
							</strong>

							<p>
								AI is evaluating the company as
								a potential AllStockControl prospect.
							</p>
						</div>
					</div>
				</section>
			`;
		}


		if (status === 'FAILED') {
			return `
				<section
					class="ai-sales-card ai-sales-qualification-card"
					id="ai-sales-company-qualification"
				>
					<div class="ai-sales-header">
						<h3>
							Company Qualification
						</h3>

						<span class="${getQualificationStatusBubble(status)}">
							FAILED
						</span>
					</div>

					<p class="ai-sales-qualification-message">
						Company qualification could not be completed.
					</p>

					<button
						type="button"
						class="button-style-agree"
						data-qualify-company-id="${
							Number(
								company.sales_company_id
							)
						}"
					>
						Retry qualification
					</button>
				</section>
			`;
		}


		if (status === 'PENDING') {
			return `
				<section
					class="ai-sales-card ai-sales-qualification-card"
					id="ai-sales-company-qualification"
				>
					<div class="ai-sales-header">
						<h3>
							Company Qualification
						</h3>

						<span class="${getQualificationStatusBubble(status)}">
							PENDING
						</span>
					</div>

					<p class="ai-sales-qualification-message">
						Waiting for automatic qualification.
					</p>
				</section>
			`;
		}


		const score =
			Number(
				company.qualification_score ?? 0
			);

		const level =
			String(
				company.qualification_level || ''
			).toUpperCase();


		return `
			<section
				class="ai-sales-card ai-sales-qualification-card"
				id="ai-sales-company-qualification"
			>
				<div class="ai-sales-header">
					<h3>
						Company Qualification
					</h3>

					<span class="${getQualificationStatusBubble(status)}">
						COMPLETED
					</span>
				</div>

				<div class="ai-sales-qualification-summary">

					<div class="ai-sales-qualification-score">
						<span>
							Score
						</span>

						<strong>
							${score}
						</strong>

						<small>
							/ 100
						</small>
					</div>


					<div class="ai-sales-qualification-level">
						<span>
							Qualification
						</span>

						<div class="${getQualificationLevelBubble(level)}">
							${escapeHtml(
								level || '-'
							)}
						</div>
					</div>

				</div>


				<div class="ai-sales-qualification-reason">
					<strong>
						Reason
					</strong>

					<p>
						${escapeHtml(
							company.qualification_reason ||
							'No qualification reason available.'
						)}
					</p>
				</div>


				${
					company.qualified_at
						? `
							<p class="ai-sales-qualification-date">
								Qualified:
								${escapeHtml(
									company.qualified_at
								)}
							</p>
						`
						: ''
				}

			</section>
		`;
	}


	function getContactSearchStatusBubble(
		status
	) {
		switch (
			String(
				status || ''
			).toUpperCase()
		) {
			case 'COMPLETED':
				return 'bubble bubble-success';

			case 'SEARCHING':
				return 'bubble bubble-info';

			case 'FAILED':
				return 'bubble bubble-danger';

			case 'NOT_FOUND':
				return 'bubble bubble-warning';

			case 'SKIPPED':
				return 'bubble bubble-neutral';

			case 'PENDING':
			default:
				return 'bubble bubble-warning';
		}
	}


	async function refreshCompanyDetails(
		salesCompanyId
	) {
		const response =
			await fetch(
				'api/get_sales_companies.php',
				{
					headers: {
						'Accept':
							'application/json'
					}
				}
			);


		const data =
			await response.json();


		if (
			!data.success ||
			!Array.isArray(data.data)
		) {
			return;
		}


		const company =
			data.data.find(
				item =>
					Number(
						item.sales_company_id
					) ===
					Number(
						salesCompanyId
					)
			);


		if (!company) {
			return;
		}


		renderCompanyDetails(
			company
		);
	}


	function renderCompanyDetails(company) {
		if (!companyDetails) return;

		companyDetails.innerHTML = `
			<div class="ai-sales-detail-layout">
				<div class="ai-sales-company-header">
					<div class="ai-sales-company-icon">
						<svg
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<rect x="4" y="3" width="11" height="18" rx="1.5"></rect>

							<path d="M15 9h4a1 1 0 0 1 1 1v11"></path>

							<path d="M8 7h1"></path>
							<path d="M12 7h1"></path>

							<path d="M8 11h1"></path>
							<path d="M12 11h1"></path>

							<path d="M8 15h1"></path>
							<path d="M12 15h1"></path>

							<path d="M9 21v-3h2v3"></path>

							<path d="M18 13h1"></path>
							<path d="M18 17h1"></path>
						</svg>
					</div>

					<div class="ai-sales-company-header-info">

						<h2>
							${escapeHtml(
								company.company_name
							)}
						</h2>

						<div class="ai-sales-company-meta">
							<span class="ai-sales-message-meta-item">
								<svg
									viewBox="0 0 24 20"
									fill="none"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"
								>
									<path d="M5 20V9a7 7 0 0 1 14 0v11"></path>
									<path d="M5 14l4-3 3 3 3-3 4 3"></path>
									<path d="M8 20v-3h8v3"></path>
								</svg>
								${escapeHtml(
									company.industry || '-'
								)}
							</span>

							<span class="ai-sales-message-meta-item">
								<svg
									viewBox="0 0 24 24"
									fill="none"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"
								>
									<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0z"></path>
									<circle cx="12" cy="10" r="2.5"></circle>
								</svg>
								${escapeHtml(
									company.city || '-'
								)},
								${escapeHtml(
									company.country || '-'
								)}
							</span>

							${
								company.market
									? `
										<span
											class="bubble"
											style="
												background: var(--alert-blue);
												color: var(--max-blue);
											"
										>
											${escapeHtml(company.market)}
										</span>
									`
									: ''
							}
						</div>

						<span class="ai-sales-message-meta-item">
							<svg
								viewBox="0 0 24 20"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"></path>
								<path d="M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.1-1.1"></path>
							</svg>
							
							${
								company.website
									? `
										<a
											href="${escapeHtml(company.website)}"
											target="_blank"
											rel="noopener noreferrer"
											class="ai-sales-company-website"
										>
											${escapeHtml(company.website)}
										</a>
									`
									: ''
							}
						</span>

						<p class="ai-sales-company-description">
							${escapeHtml(
								stripHtmlTags(
									company.description ||
									'No description available.'
								)
							)}
						</p>

					</div>
				</div>


				<div class="ai-sales-summary-grid">
					<section class="ai-sales-card">
						<h3 class="ai-sales-card-title">
							<span class="ai-sales-message-meta-item">
								<svg
									style="width: 20px; height: 20px;"
									viewBox="0 0 24 24"
									fill="none"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round"
								>
									<rect x="5" y="3" width="14" height="18" rx="1"></rect>
									<path d="M9 7h2"></path>
									<path d="M13 7h2"></path>
									<path d="M9 11h2"></path>
									<path d="M13 11h2"></path>
									<path d="M9 15h2"></path>
									<path d="M13 15h2"></path>
									<path d="M10 21v-3h4v3"></path>
								</svg>
								Company information
							</span>
						</h3>

						<div class="ai-sales-info-row">
							<span>Market</span>

							<strong>
								${escapeHtml(
									company.market || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Country</span>

							<strong>
								${escapeHtml(
									company.country || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>City</span>

							<strong>
								${escapeHtml(
									company.city || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Industry</span>

							<strong>
								${escapeHtml(
									company.industry || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Language</span>

							<strong>
								${escapeHtml(
									company.language || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Source</span>

							<strong>
								${escapeHtml(
									company.source || '-'
								)}
							</strong>
						</div>

					</section>


					<section
						class="ai-sales-card"
						id="ai-sales-contacts"
					>
						<p>
							Loading contacts...
						</p>
					</section>


					<section
						class="ai-sales-card"
						id="ai-sales-leads"
					>
						<p>
							Loading sales opportunity...
						</p>
					</section>

				</div>

				${renderCompanyResearch(company)}

				${renderCompanyQualification(company)}

				<div
					id="ai-sales-conversations-panel"
					class="ai-sales-conversations-panel"
				>
				</div>

			</div>
		`;

		loadSalesContacts(
			company
		);

		loadSalesLeads(
			company.sales_company_id
		);

		const researchButton =
			document.querySelector(
				'[data-research-company-id]'
			);


		researchButton?.addEventListener(
			'click',
			async () => {
				const salesCompanyId =
					Number(
						researchButton
							.dataset
							.researchCompanyId
					);

				if (salesCompanyId <= 0) {
					return;
				}


				researchButton.disabled =
					true;

				researchButton.textContent =
					'Researching...';


				const researchContainer =
					document.getElementById(
						'ai-sales-company-research'
					);


				if (researchContainer) {
					researchContainer.innerHTML = `
						<div class="ai-sales-processing">
							<img
								src="images/sys-img/loading2.gif"
								alt="Processing"
								class="ai-sales-processing-gif"
							>

							<div>
								<strong>
									Researching company...
								</strong>

								<p>
									AI is analyzing company information
									and verified sources.
								</p>
							</div>
						</div>
					`;
				}


				try {
					await researchProspect(
						salesCompanyId
					);


					salesContactsCache.delete(
						salesCompanyId
					);


					await refreshCompanyDetails(
						salesCompanyId
					);

				} catch (error) {
					console.error(
						'Error researching company:',
						error
					);

					await refreshCompanyDetails(
						salesCompanyId
					);
				}
			}
		);


		const qualificationButton = document.querySelector('[data-qualify-company-id]');

		qualificationButton?.addEventListener(
			'click',
			async () => {
				const salesCompanyId =
					Number(
						qualificationButton
							.dataset
							.qualifyCompanyId
					);


				if (salesCompanyId <= 0) {
					return;
				}


				qualificationButton.disabled =
					true;

				qualificationButton.textContent =
					'Qualifying...';


				const qualificationContainer =
					document.getElementById(
						'ai-sales-company-qualification'
					);


				if (qualificationContainer) {
					qualificationContainer.innerHTML = `
						<div class="ai-sales-processing">
							<img
								src="images/sys-img/loading2.gif"
								alt="Processing"
								class="ai-sales-processing-gif"
							>

							<div>
								<strong>
									Qualifying company...
								</strong>

								<p>
									AI is evaluating the company as
									a potential AllStockControl prospect.
								</p>
							</div>
						</div>
					`;
				}


				try {
					await qualifyProspect(
						salesCompanyId
					);


					salesContactsCache.delete(
						salesCompanyId
					);


					await refreshCompanyDetails(
						salesCompanyId
					);

				} catch (error) {
					console.error(
						'Error qualifying company:',
						error
					);


					await refreshCompanyDetails(
						salesCompanyId
					);
				}
			}
		);


		const researchSummary = document.getElementById('ai-sales-research-summary');

		const researchSummaryToggle = document.querySelector('[data-research-summary-toggle]');

		if (
			researchSummary &&
			researchSummaryToggle
		) {
			const hasOverflow =
				researchSummary.scrollHeight >
				researchSummary.clientHeight + 1;


			if (hasOverflow) {
				researchSummaryToggle.hidden =
					false;
			}


			researchSummaryToggle.addEventListener(
				'click',
				() => {
					const isExpanded =
						researchSummary.classList.toggle(
							'is-expanded'
						);


					researchSummaryToggle.textContent =
						isExpanded
							? 'Read less'
							: 'Read more';
				}
			);
		}
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
					<h3 class="ai-sales-card-title">
						<span class="ai-sales-message-meta-item">
							<svg
								style="width: 20px; height: 20px;"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle cx="10.5" cy="13.5" r="6.5"></circle>
								<circle cx="10.5" cy="13.5" r="3"></circle>
								<circle cx="10.5" cy="13.5" r="0.8" fill="currentColor" stroke="none"></circle>

								<path d="M10.5 13.5L18 6"></path>
								<path d="M17.5 3.5v3h3"></path>
							</svg>
							Sales Opportunity
						</span>
					</h3>

					<p>No sales opportunity found.</p>
				`;

				return;
			}

			leadsContainer.innerHTML = `
				<h3 class="ai-sales-card-title">
					<span class="ai-sales-message-meta-item">
						<svg
							style="width: 20px; height: 20px;"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<circle cx="10.5" cy="13.5" r="6.5"></circle>
							<circle cx="10.5" cy="13.5" r="3"></circle>
							<circle cx="10.5" cy="13.5" r="0.8" fill="currentColor" stroke="none"></circle>

							<path d="M10.5 13.5L18 6"></path>
							<path d="M17.5 3.5v3h3"></path>
						</svg>
						Sales Opportunity
					</span>
				</h3>

				${data.data.map(lead => `
					<div class="ai-sales-lead">
						<div class="ai-sales-info-row">
							<span>Stage</span>

							<div
								class="${getBubbleClass(
									lead.stage || 'NEW'
								)}"
							>
								${escapeHtml(
									lead.stage || 'NEW'
								)}
							</div>
						</div>

						<div class="ai-sales-info-row">
							<span>Score</span>

							<strong>
								${Number(
									lead.score ?? 0
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Source</span>

							<strong>
								${escapeHtml(
									lead.source || '-'
								)}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Next action</span>

							<div class="ai-sales-info-value">
								${escapeHtml(
									lead.next_action || '-'
								)}
							</div>
						</div>

						<div class="ai-sales-info-row">
							<span>Last contact</span>

							<div class="ai-sales-info-value">
								${escapeHtml(
									lead.last_contact_at || '-'
								)}
							</div>
						</div>

						${
							lead.score_reason
								? `
									<div class="ai-sales-info-row">
										<span>Score reason</span>

										<div class="ai-sales-info-value">
											${escapeHtml(
												lead.score_reason
											)}
										</div>
									</div>
								`
								: ''
						}

						${
							lead.ai_summary
								? `
									<div class="ai-sales-info-row">
										<span>AI Summary</span>

										<div class="ai-sales-info-value">
											${escapeHtml(
												lead.ai_summary
											)}
										</div>
									</div>
								`
								: ''
						}
					</div>
				`).join('')}
			`;

			const conversationsPanel =
				document.getElementById(
					'ai-sales-conversations-panel'
				);

			if (conversationsPanel) {
				conversationsPanel.innerHTML =
					data.data.map(lead => `
						<div
							id="ai-sales-conversations-${lead.sales_lead_id}"
							class="ai-sales-conversations"
						>
						</div>
					`).join('');
			}

			data.data.forEach(lead => {
				loadSalesConversations(lead.sales_lead_id, salesCompanyId);
			});

		} catch (error) {
			console.error('Error loading sales leads:', error);

			leadsContainer.innerHTML = `
				<h3 class="ai-sales-card-title">
					<span class="ai-sales-message-meta-item">
						<svg
							style="width: 20px; height: 20px;"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<circle cx="10.5" cy="13.5" r="6.5"></circle>
							<circle cx="10.5" cy="13.5" r="3"></circle>
							<circle cx="10.5" cy="13.5" r="0.8" fill="currentColor" stroke="none"></circle>

							<path d="M10.5 13.5L18 6"></path>
							<path d="M17.5 3.5v3h3"></path>
						</svg>
						Sales Opportunity
					</span>
				</h3>

				<p style="color:red;">
					Error loading sales opportunity.
				</p>
			`;
		}
	}


	async function loadSalesConversations(
		salesLeadId,
		salesCompanyId
	) {
		const conversationsContainer =
			document.getElementById(
				`ai-sales-conversations-${salesLeadId}`
			);

		if (!conversationsContainer) {
			return;
		}

		conversationsContainer.innerHTML = `
			<p>Loading conversations...</p>
		`;

		try {
			const response = await fetch(
				`api/get_sales_conversations.php?sales_lead_id=${salesLeadId}`,
				{
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}
			);

			const data =
				await response.json();

			if (
				!data.success ||
				!Array.isArray(data.data) ||
				data.data.length === 0
			) {
				conversationsContainer.innerHTML = `
					<h3>
						<span class="ai-sales-message-meta-item">
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<path d="M4 5.5h10a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3H9l-4 3v-3.5a3 3 0 0 1-1-2.2V8.5a3 3 0 0 1 3-3z"></path>
								<path d="M14 9h3a3 3 0 0 1 3 3v2.5a3 3 0 0 1-3 3h-1l-3 2v-2"></path>
								<path d="M8 9.5h5"></path>
							</svg>
							Conversations
						</span>
					</h3>

					<p>
						No conversations found.
					</p>
				`;

				return;
			}

			conversationsContainer.innerHTML = `
				<h3>
					<span class="ai-sales-message-meta-item">
						<svg
							style="width: 25px; height: 25px;"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<rect x="3" y="5" width="13" height="10" rx="3"></rect>

							<path d="M6 15v3l4-3"></path>

							<path d="M8 9h5"></path>

							<path d="M17 9h1a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3h-1v2l-3-2"></path>
						</svg>
						Conversations
					</span>
				</h3>

				${data.data.map(conversation => `
					<div class="ai-sales-conversation">
						<div class="ai-sales-conversation-header">
							<div class="ai-sales-conversation-icon">
								<svg
									viewBox="0 0 24 24"
									fill="none"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"
								>
									<rect x="3" y="5" width="18" height="14" rx="2"></rect>
									<path d="M3 7l9 6 9-6"></path>
								</svg>
							</div>
							<div class="ai-sales-conversation-header-info">
								<div class="inline-group">
									<strong>
										${escapeHtml(
											conversation.subject || '-'
										)}
									</strong>
									<div class="${getBubbleClass(
											conversation.status || ''
										)}"
									>
										${escapeHtml(
											conversation.status || '-'
										)}
									</div>
								</div>
								<div class="inline-group">
									<div class="${getBubbleClass(
											conversation.channel || ''
										)}"
									>
										${escapeHtml(
											conversation.channel || '-'
										)}
									</div>
								
									<small>
										<span class="ai-sales-message-meta-item">
											<svg
												viewBox="0 0 24 24"
												fill="none"
												stroke="currentColor"
												stroke-width="2.1"
												stroke-linecap="round"
												stroke-linejoin="round"
											>
												<rect x="4" y="5" width="16" height="15" rx="2"></rect>
												<path d="M8 3.5v3"></path>
												<path d="M16 3.5v3"></path>
												<path d="M4 9.5h16"></path>
											</svg>
											Started: 
											${escapeHtml(
												conversation.started_at || '-'
											)}
										</span>
									</small>
								</div>
							</div>
						</div>
						<div
							id="ai-sales-messages-${conversation.conversation_id}"
							class="ai-sales-messages"
						>
						</div>
					</div>
				`).join('')}
			`;

			data.data.forEach(conversation => {
				loadSalesMessages(
					conversation.conversation_id,
					salesCompanyId,
					conversation.sales_contact_id
				);
			});

		} catch (error) {
			console.error(
				'Error loading sales conversations:',
				error
			);

			conversationsContainer.innerHTML = `
				<h3>Conversations</h3>

				<p style="color:red;">
					Error loading conversations.
				</p>
			`;
		}
	}

	async function loadSalesMessages(
		conversationId,
		salesCompanyId,
		salesContactId = null
	) {
		const messagesContainer = document.getElementById(`ai-sales-messages-${conversationId}`);

		if (!messagesContainer) {
			return;
		}

		messagesContainer.innerHTML = `
			<p>Loading messages...</p>
		`;

		try {
			const response = await fetch(
				`api/get_sales_messages.php?conversation_id=${conversationId}`,
				{
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}
			);

			const data =
				await response.json();

			if (
				!data.success ||
				!Array.isArray(data.data) ||
				data.data.length === 0
			) {
				messagesContainer.innerHTML = `
					<h4>Messages</h4>

					<p>
						No messages found.
					</p>
				`;

				return;
			}

			const latestMessage =
				data.data[
					data.data.length - 1
				];

			const canGenerateAiReply =
				latestMessage.direction === 'INBOUND' &&
				latestMessage.status === 'RECEIVED';

			let contactName = 'CONTACT';

			if (Number(salesContactId) > 0) {
				try {
					const contacts = await getSalesContacts(salesCompanyId);

					const contact = contacts.find(
						item =>
							Number(item.sales_contact_id) ===
							Number(salesContactId)
					);

					if (
						contact &&
						String(contact.full_name || '').trim()
					) {
						contactName = String(contact.full_name).trim();
					}
				} catch (error) {
					console.warn(
						'Could not resolve sales contact:',
						error
					);
				}
			}

			
			messagesContainer.innerHTML = `
				<h4>Messages</h4>

				${data.data.map((message, index) => `
					<div
						class="ai-sales-message ${
							message.direction === 'INBOUND'
								? 'ai-sales-message--inbound'
								: ''
						}"
						data-message-id="${message.sales_message_id}"
					>

						<div class="ai-sales-message-avatar">
							${
								message.sender_type === 'AI'
									? `
										<svg
											viewBox="0 0 24 22"
											fill="none"
											stroke="currentColor"
											stroke-width="1.6"
											stroke-linecap="round"
											stroke-linejoin="round"
										>
											<path d="M9 3h6"></path>
											<path d="M12 3v2"></path>

											<path d="M7 6h10a3 3 0 0 1 3 3v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V9a3 3 0 0 1 3-3z"></path>

											<path d="M4 10H3"></path>
											<path d="M21 10h-1"></path>

											<path d="M9 11.5h.01"></path>
											<path d="M15 11.5h.01"></path>

											<path d="M10 15c.6.5 1 .7 2 .7s1.4-.2 2-.7"></path>
										</svg>
									`
									: `
										<svg
											viewBox="0 0 24 24"
											fill="none"
											stroke="currentColor"
											stroke-width="1.6"
											stroke-linecap="round"
											stroke-linejoin="round"
										>
											<circle cx="12" cy="8" r="3"></circle>
											<path d="M5 20c0-4 3-7 7-7s7 3 7 7"></path>
										</svg>
									`
							}
						</div>

						<div class="ai-sales-message-main">

							<div class="ai-sales-message-header">

								<strong class="ai-sales-message-sender">
									${escapeHtml(
										message.sender_type === 'CONTACT'
											? contactName
											: (message.sender_type || 'Unknown')
									)}
								</strong>

								<div class="ai-sales-message-badges">
									<span class="${getBubbleClass(message.direction)}">
										${escapeHtml(message.direction || '-')}
									</span>

									<span class="${getBubbleClass(message.status)}">
										${escapeHtml(message.status || 'DRAFT')}
									</span>
								</div>

							</div>

							${
								message.subject
									? `
										<strong class="ai-sales-message-subject">
											${escapeHtml(message.subject)}
										</strong>
									`
									: ''
							}

							<p class="ai-sales-message-text">${escapeHtml(message.message || '')}</p>

							<div class="ai-sales-message-meta">

								${
									message.ai_generated
										? `
											<span class="ai-sales-message-meta-item">
												<svg
													viewBox="0 0 24 24"
													fill="none"
													stroke="currentColor"
													stroke-width="2"
													stroke-linecap="round"
													stroke-linejoin="round"
												>
													<path d="M12 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"></path>
													<path d="M18 12l.7 2.1L21 15l-2.3.9L18 18l-.7-2.1L15 15l2.3-.9L18 12z"></path>
													<path d="M6 13l.6 1.8L8.5 15.5l-1.9.7L6 18l-.6-1.8-1.9-.7 1.9-.7L6 13z"></path>
												</svg>
												AI generated
											</span>
										`
										: ''
								}

								${
									message.status === 'SENT'
										? `
											<span class="ai-sales-message-meta-item">
												<svg
													viewBox="0 0 24 24"
													fill="none"
													stroke="currentColor"
													stroke-width="2"
													stroke-linecap="round"
													stroke-linejoin="round"
												>
													<path d="M5 12l4 4L19 6"></path>
												</svg>
												Message sent
											</span>
										`
										: ''
								}

								${
									message.sent_at
										? `
											<span class="ai-sales-message-meta-item">
												<svg
													viewBox="0 0 24 24"
													fill="none"
													stroke="currentColor"
													stroke-width="2"
													stroke-linecap="round"
													stroke-linejoin="round"
												>
													<circle cx="12" cy="12" r="8"></circle>
													<path d="M12 8v4l3 2"></path>
												</svg>
												${escapeHtml(message.sent_at)}
											</span>
										`
										: ''
								}

								${
									message.received_at
										? `
											<span class="ai-sales-message-meta-item">
												<svg
													viewBox="0 0 24 24"
													fill="none"
													stroke="currentColor"
													stroke-width="2"
													stroke-linecap="round"
													stroke-linejoin="round"
												>
													<circle cx="12" cy="12" r="8"></circle>
													<path d="M12 8v4l3 2"></path>
												</svg>
												${escapeHtml(message.received_at)}
											</span>
										`
										: ''
								}

							</div>

							${
								message.status === 'PENDING_APPROVAL'
									? `
										<div class="ai-sales-message-actions">
											<button
												type="button"
												class="button-style-agree approve-sales-message-btn"
												data-message-id="${message.sales_message_id}"
											>
												Approve
											</button>
										</div>
									`
									: ''
							}

							${
								message.status === 'APPROVED'
									? `
										<div class="ai-sales-message-actions">
											<p>
												<small>Message approved</small>
											</p>

											<button
												type="button"
												class="button-style-agree send-sales-message-btn"
												data-message-id="${message.sales_message_id}"
											>
												Send (test)
											</button>
										</div>
									`
									: ''
							}

							${
								canGenerateAiReply &&
								index === data.data.length - 1
									? `
										<div class="ai-sales-message-actions">
											<button
												type="button"
												class="button-style-agree generate-ai-sales-reply-btn"
												data-conversation-id="${conversationId}"
											>
												Generate AI reply
											</button>
										</div>
									`
									: ''
							}

						</div>
					</div>
				`).join('')}
			`;


			const generateReplyButtons =
				messagesContainer.querySelectorAll(
					'.generate-ai-sales-reply-btn'
				);

			generateReplyButtons.forEach(button => {
				button.addEventListener(
					'click',
					async () => {
						await generateAiSalesReply(
							conversationId,
							salesCompanyId,
							salesContactId,
							button
						);
					}
				);
			});


			const approveButtons = messagesContainer.querySelectorAll('.approve-sales-message-btn');
			approveButtons.forEach(button => {
				button.addEventListener(
					'click',
					async () => {
						const salesMessageId = Number(button.dataset.messageId);

						if (!salesMessageId) {
							return;
						}

						await approveSalesMessage(
							salesMessageId,
							conversationId,
							salesCompanyId,
							salesContactId,
							button
						);
					}
				);

			});

			const sendButtons = messagesContainer.querySelectorAll('.send-sales-message-btn');
			sendButtons.forEach(button => {
				button.addEventListener(
					'click',
					async () => {
						const salesMessageId = Number(button.dataset.messageId);

						if (!salesMessageId) {
							return;
						}

						await sendSalesMessage(
							salesMessageId,
							salesCompanyId,
							button
						);
					}
				);

			});

		} catch (error) {
			console.error(
				'Error loading sales messages:',
				error
			);

			messagesContainer.innerHTML = `
				<h4>Messages</h4>

				<p style="color:red;">
					Error loading messages.
				</p>
			`;
		}
	}

	async function generateAiSalesReply(
		conversationId,
		salesCompanyId,
		salesContactId,
		button
	) {
		const originalText =
			button.textContent;

		button.disabled = true;
		button.textContent =
			'Generating...';

		try {
			const formData =
				new FormData();

			formData.append(
				'conversation_id',
				conversationId
			);


			const response =
				await fetch(
					'api/generate_sales_reply.php',
					{
						method: 'POST',
						headers: {
							'Accept':
								'application/json'
						},
						body: formData
					}
				);


			const data =
				await response.json();


			if (!data.success) {
				throw new Error(
					data.message ||
					'Could not generate AI sales reply.'
				);
			}


			await loadSalesMessages(
				conversationId,
				salesCompanyId,
				salesContactId
			);

		} catch (error) {
			console.error(
				'Error generating AI sales reply:',
				error
			);

			alert(
				error.message ||
				'Error generating AI sales reply.'
			);

			button.disabled = false;
			button.textContent =
				originalText;
		}
	}

	async function approveSalesMessage(
		salesMessageId,
		conversationId,
		salesCompanyId,
		salesContactId,
		button
	) {
		const originalText =
			button.textContent;

		button.disabled = true;
		button.textContent = 'Approving...';

		try {
			const formData =
				new FormData();

			formData.append(
				'sales_message_id',
				salesMessageId
			);

			const response = await fetch(
				'api/approve_sales_message.php',
				{
					method: 'POST',
					headers: {
						'Accept': 'application/json'
					},
					body: formData
				}
			);

			const data =
				await response.json();

			if (!data.success) {
				throw new Error(
					data.message ||
					'Could not approve message.'
				);
			}

			await loadSalesMessages(
				conversationId,
				salesCompanyId,
				salesContactId
			);

		} catch (error) {
			console.error(
				'Error approving sales message:',
				error
			);

			alert(
				error.message ||
				'Error approving sales message.'
			);

			button.disabled = false;
			button.textContent =
				originalText;
		}
	}

	async function sendSalesMessage(
		salesMessageId,
		salesCompanyId,
		button
	) {
		const originalText =
			button.textContent;

		button.disabled = true;
		button.textContent = 'Sending...';

		try {
			const formData =
				new FormData();

			formData.append(
				'sales_message_id',
				salesMessageId
			);

			const response = await fetch(
				'api/send_sales_message.php',
				{
					method: 'POST',
					headers: {
						'Accept': 'application/json'
					},
					body: formData
				}
			);

			const data =
				await response.json();

			if (!data.success) {
				throw new Error(
					data.message ||
					'Could not send message.'
				);
			}

			await loadSalesLeads(
				salesCompanyId
			);

		} catch (error) {
			console.error(
				'Error sending sales message:',
				error
			);

			alert(
				error.message ||
				'Error sending sales message.'
			);

			button.disabled = false;
			button.textContent =
				originalText;
		}
	}

	
	async function loadSalesContacts(
		company
	) {
		const contactsContainer =
			document.getElementById(
				'ai-sales-contacts'
			);


		if (!contactsContainer) {
			return;
		}


		const salesCompanyId =
			Number(
				company.sales_company_id
			);


		const qualificationStatus =
			String(
				company.qualification_status ||
					'PENDING'
			).toUpperCase();


		const qualificationLevel =
			String(
				company.qualification_level || ''
			).toUpperCase();


		let contactSearchStatus =
			String(
				company.contact_search_status ||
					'PENDING'
			).toUpperCase();


		/*
		 * Compatibility with companies qualified
		 * before SKIPPED was introduced.
		 */
		if (
			contactSearchStatus === 'PENDING' &&
			qualificationStatus === 'COMPLETED' &&
			qualificationLevel === 'LOW'
		) {
			contactSearchStatus =
				'SKIPPED';
		}


		contactsContainer.innerHTML = `
			<h3 class="ai-sales-card-title">
				<span class="ai-sales-message-meta-item">
					<svg
						style="width: 20px; height: 20px;"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="1.6"
						stroke-linecap="round"
						stroke-linejoin="round"
					>
						<circle
							cx="12"
							cy="7.5"
							r="3"
						></circle>

						<path
							d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"
						></path>

						<path
							d="M6 20h12"
						></path>
					</svg>

					Contacts
				</span>
			</h3>

			<p>
				Loading contacts...
			</p>
		`;


		try {
			const contacts =
				await getSalesContacts(
					salesCompanyId
				);


			/*
			 * If contacts already exist, always show them.
			 */
			if (contacts.length > 0) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">

						<span class="ai-sales-message-meta-item">
							<svg
								style="width: 20px; height: 20px;"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="1.6"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle
									cx="12"
									cy="7.5"
									r="3"
								></circle>

								<path
									d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"
								></path>

								<path
									d="M6 20h12"
								></path>
							</svg>

							Contacts
						</span>


						${
							contactSearchStatus !== 'PENDING'
								? `
									<span
										class="${getContactSearchStatusBubble(
											contactSearchStatus
										)}"
									>
										${escapeHtml(
											contactSearchStatus.replace(
												'_',
												' '
											)
										)}
									</span>
								`
								: ''
						}

					</div>


					${
						contactSearchStatus === 'SEARCHING'
							? `
								<div class="ai-sales-contact-search-inline">
									<strong>
										Searching for additional contacts...
									</strong>
								</div>
							`
							: ''
					}


					${contacts.map(contact => `
						<div class="ai-sales-contact">

							<strong class="ai-sales-contact-name">
								${escapeHtml(
									contact.full_name ||
									'Unknown contact'
								)}
							</strong>


							<p>
								${escapeHtml(
									contact.job_title || '-'
								)}
							</p>


							<p class="ai-sales-contact-value">
								<span class="ai-sales-message-meta-item">
									<svg
										viewBox="0 0 24 24"
										fill="none"
										stroke="currentColor"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round"
									>
										<rect
											x="3"
											y="5"
											width="18"
											height="14"
											rx="2"
										></rect>

										<path
											d="M3 7l9 6 9-6"
										></path>
									</svg>

									${escapeHtml(
										contact.email || '-'
									)}
								</span>
							</p>


							<p class="ai-sales-contact-value">
								<span class="ai-sales-message-meta-item">
									<svg
										viewBox="0 0 24 24"
										fill="none"
										stroke="currentColor"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round"
									>
										<path
											d="M5 4h4l2 5-3 2a15 15 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2C10.3 21 3 13.7 3 6a2 2 0 0 1 2-2z"
										></path>
									</svg>

									${escapeHtml(
										contact.phone || '-'
									)}
								</span>
							</p>


							${
								contact.linkedin_url
									? `
										<p class="ai-sales-contact-value">
											<a
												href="${escapeHtml(
													contact.linkedin_url
												)}"
												target="_blank"
												rel="noopener noreferrer"
											>
												LinkedIn
											</a>
										</p>
									`
									: ''
							}


							${
								contact.source_url
									? `
										<p class="ai-sales-contact-source">
											Source:

											<a
												href="${escapeHtml(
													contact.source_url
												)}"
												target="_blank"
												rel="noopener noreferrer"
											>
												${escapeHtml(
													contact.source_type ||
													'WEB'
												)}
											</a>
										</p>
									`
									: ''
							}


							${
								contact.is_primary
									? `
										<div class="bubble bubble-success">
											Primary contact
										</div>
									`
									: ''
							}

						</div>
					`).join('')}

				`;

				return;
			}


			/*
			 * No contacts exist.
			 */

			if (
				qualificationStatus !==
					'COMPLETED'
			) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">
						<span>
							Contacts
						</span>

						<span class="bubble bubble-neutral">
							WAITING
						</span>
					</div>

					<p class="ai-sales-contact-search-message">
						Company qualification must be completed first.
					</p>
				`;

				return;
			}


			if (
				contactSearchStatus ===
					'SEARCHING'
			) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">
						<span>
							Contacts
						</span>

						<span
							class="${getContactSearchStatusBubble(
								contactSearchStatus
							)}"
						>
							SEARCHING
						</span>
					</div>


					<div class="ai-sales-processing">
						<img
							src="images/sys-img/loading2.gif"
							alt="Processing"
							class="ai-sales-processing-gif"
						>

						<div>
							<strong>
								Searching for contacts...
							</strong>

							<p>
								AI is looking for relevant
								company contacts.
							</p>
						</div>
					</div>
				`;

				return;
			}


			if (
				contactSearchStatus ===
					'FAILED'
			) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">
						<span>
							Contacts
						</span>

						<span
							class="${getContactSearchStatusBubble(
								contactSearchStatus
							)}"
						>
							FAILED
						</span>
					</div>


					<p class="ai-sales-contact-search-message">
						Contact search could not be completed.
					</p>


					<button
						type="button"
						class="button-style-agree"
						data-find-contacts-company-id="${salesCompanyId}"
					>
						Retry contact search
					</button>
				`;


				const retryButton =
					contactsContainer.querySelector(
						'[data-find-contacts-company-id]'
					);


				retryButton?.addEventListener(
					'click',
					async () => {
						retryButton.disabled =
							true;

						retryButton.textContent =
							'Searching...';


						try {
							await findContactsProspect(
								salesCompanyId
							);


							salesContactsCache.delete(
								salesCompanyId
							);


							await refreshCompanyDetails(
								salesCompanyId
							);

						} catch (error) {
							console.error(
								'Error searching company contacts:',
								error
							);


							await refreshCompanyDetails(
								salesCompanyId
							);
						}
					}
				);


				return;
			}


			if (
				contactSearchStatus ===
					'NOT_FOUND'
			) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">
						<span class="ai-sales-message-meta-item">
							<svg
								style="width: 20px; height: 20px;"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="1.6"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle cx="12" cy="7.5" r="3"></circle>
								<path d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"></path>
								<path d="M6 20h12"></path>
							</svg>
							
							Contacts
						</span>

						<span
							class="${getContactSearchStatusBubble(
								contactSearchStatus
							)}"
						>
							NOT FOUND
						</span>
					</div>


					<p class="ai-sales-contact-search-message">
						No verified relevant contacts were found.
					</p>


					${
						company.contact_searched_at
							? `
								<p class="ai-sales-contact-search-date">
									Searched:
									${escapeHtml(
										company.contact_searched_at
									)}
								</p>
							`
							: ''
					}
				`;

				return;
			}


			if (
				contactSearchStatus ===
					'SKIPPED'
			) {
				contactsContainer.innerHTML = `
					<div class="ai-sales-card-title ai-sales-contacts-header">
						<span class="ai-sales-message-meta-item">
							<svg
								style="width: 20px; height: 20px;"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="1.6"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle cx="12" cy="7.5" r="3"></circle>
								<path d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"></path>
								<path d="M6 20h12"></path>
							</svg>

							Contacts
						</span>

						<span
							class="${getContactSearchStatusBubble(
								contactSearchStatus
							)}"
						>
							SKIPPED
						</span>
					</div>


					<p class="ai-sales-contact-search-message">
						Automatic contact search was skipped
						because the company received a LOW
						qualification.
					</p>
				`;

				return;
			}


			contactsContainer.innerHTML = `
				<div class="ai-sales-card-title ai-sales-contacts-header">
					<span class="ai-sales-message-meta-item">
						<svg
							style="width: 20px; height: 20px;"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="1.6"
							stroke-linecap="round"
							stroke-linejoin="round"
						>
							<circle cx="12" cy="7.5" r="3"></circle>
							<path d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"></path>
							<path d="M6 20h12"></path>
						</svg>

						Contacts
					</span>

					<span
						class="${getContactSearchStatusBubble(
							contactSearchStatus
						)}"
					>
						PENDING
					</span>
				</div>


				<p class="ai-sales-contact-search-message">
					Waiting for automatic contact search.
				</p>
			`;

		} catch (error) {
			console.error(
				'Error loading sales contacts:',
				error
			);


			contactsContainer.innerHTML = `
				<h3>
					Contacts
				</h3>

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

					const row = document.createElement('tr');
					row.className = 'users-row ai-sales-company-row';

					row.dataset.companyId = String(company.sales_company_id);

					row.innerHTML = `
						<td valign="middle" style="width: calc(55% - 10px); padding-left: 10px;">
							<strong>
								${escapeHtml(company.company_name)}
							</strong>

							<p class="mini-title">
								${escapeHtml(company.industry || 'Unknown industry')}
							</p>
						</td>

						<td style="width: 20%;">
							${escapeHtml(company.country || '-')}
						</td>

						<td align="center" style="width: calc(20% - 10px); padding-right: 10px;">
							${escapeHtml(company.market || '-')}
						</td>
					`;

					row.addEventListener('click', () => {
							document.querySelectorAll('.ai-sales-company-row')
								.forEach(item => {
									item.classList.remove(
										'selected-user'
									);
								});

							row.classList.add('selected-user');

							renderCompanyDetails(company);
						}
					);

					companyContainer.appendChild(row);
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

	openProspectSearchBtn?.addEventListener(
		'click',
		renderProspectSearch
	);

	searchCompanyField?.addEventListener(
		'keyup',
		fetchAndRenderCompanies
	);

	await fetchAndRenderCompanies();
};