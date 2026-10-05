window.loadAiSales = async function () {
	const companyContainer = document.getElementById('ai-sales-company-list');
	const searchCompanyField = document.getElementById('searchSalesCompanyField');

	const companyDetails = document.getElementById('ai-sales-details');

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
			<div class="ai-sales-detail-layout">
				<div class="ai-sales-company-header">

					<div class="ai-sales-company-icon">
						<i class="fa-solid fa-building"></i>
					</div>

					<div class="ai-sales-company-header-info">
						<h2>${escapeHtml(company.company_name)}</h2>

						<div class="ai-sales-company-meta">
							<span>
								${escapeHtml(company.industry || '-')}
							</span>

							<span>
								${escapeHtml(company.city || '-')}
							</span>

							<span>
								${escapeHtml(company.country || '-')}
							</span>
						</div>

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

						<p class="ai-sales-company-description">
							${escapeHtml(company.description || 'No description available.')}
						</p>
					</div>
				</div>


				<div class="ai-sales-summary-grid">
					<section class="ai-sales-card">
						<h3>Company information</h3>

						<div class="ai-sales-info-row">
							<span>Market</span>
							<strong>
								${escapeHtml(company.market || '-')}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Country</span>
							<strong>
								${escapeHtml(company.country || '-')}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>City</span>
							<strong>
								${escapeHtml(company.city || '-')}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Industry</span>
							<strong>
								${escapeHtml(company.industry || '-')}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Language</span>
							<strong>
								${escapeHtml(company.language || '-')}
							</strong>
						</div>

						<div class="ai-sales-info-row">
							<span>Source</span>
							<strong>
								${escapeHtml(company.source || '-')}
							</strong>
						</div>
					</section>


					<section
						class="ai-sales-card"
						id="ai-sales-contacts"
					>
						<p>Loading contacts...</p>
					</section>


					<section
						class="ai-sales-card"
						id="ai-sales-leads"
					>
						<p>Loading sales opportunity...</p>
					</section>
				</div>

				<div
					id="ai-sales-conversations-panel"
					class="ai-sales-conversations-panel"
				>
				</div>

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
					<h3>Sales Opportunity</h3>

					<p>No sales opportunity found.</p>
				`;

				return;
			}

			leadsContainer.innerHTML = `
				<h3>Sales Opportunity</h3>

				${data.data.map(lead => `
					<div class="ai-sales-lead">
						<div class="ai-sales-info-row">
							<span>Stage:</span>
							<div class="bubble" style="background: var(--alert-blue); color: var(--max-blue);">
								${escapeHtml(lead.stage || 'NEW')}
							</div>
						</div>
						<div class="ai-sales-info-row">
							<span>Score:</span>
							<strong>
								${Number(lead.score ?? 0)}
							</strong>
						</div>
						<div class="ai-sales-info-row">
							<span>Source:</span>
							<strong>
								${escapeHtml(lead.source || '-')}
							</strong>
						</div>
						<div class="ai-sales-info-row">
							<span>Next action:</span>
							
							${escapeHtml(lead.next_action || '-')}
						</div>
						<div class="ai-sales-info-row">
							<span>Last contact:</span>
							
							${escapeHtml(lead.last_contact_at || '-')}
						</div>
							
						${
							lead.score_reason
								? `
									<div class="ai-sales-info-row">
										<span>Score reason:</span>
										<strong>
											${escapeHtml(lead.score_reason)}
										</strong>
									</div>
								`
								: ''
						}

						${
							lead.ai_summary
								? `
									<div class="ai-sales-info-row">
										<span>AI Summary:</span>
										<strong>
											${escapeHtml(lead.ai_summary)}
										</strong>
									</div>
								`
								: ''
						}
						</table>
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
				<h3>Sales Opportunity</h3>

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
					<h3>Conversations</h3>

					<p>
						No conversations found.
					</p>
				`;

				return;
			}

			conversationsContainer.innerHTML = `
				<h3>Conversations</h3>

				${data.data.map(conversation => `
					<div class="ai-sales-conversation">
						<div class="ai-sales-conversation-header">
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td colspan="1" align="left" valign="middle">
										<div class="inline-group">
											<strong>
												${escapeHtml(
													conversation.subject || '-'
												)}
											</strong>
											<div class="bubble" style="background: var(--warning-yellow); color: var(--warning-orange);">
												${escapeHtml(
													conversation.status || '-'
												)}
											</div>
										</div>
										<div class="inline-group">
											<div class="bubble" style="background: var(--alert-blue); color: var(--max-blue);">
												${escapeHtml(
													conversation.channel || '-'
												)}
											</div>
										
											<small>
												Started: 
												${escapeHtml(
													conversation.started_at || '-'
												)}
											</small>
										</div>
									</td>
								</tr>
							</table>
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
					salesCompanyId
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
		salesCompanyId
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

			messagesContainer.innerHTML = `
				<h4>Messages</h4>

				${data.data.map(message => `
					<div
						class="ai-sales-message"
						data-message-id="${message.sales_message_id}"
					>
						<div class="ai-sales-message-type">
							<div class="ai-sales-message-avatar">
							
							</div>
							<strong>
								${escapeHtml(
									message.sender_type || 'Unknown'
								)}
							</strong>
						</div>
						<div class="ai-sales-message-content">
							${
								message.subject
									? `
										<strong>

											${escapeHtml(
												message.subject
											)}
										</strong>
									`
									: ''
							}

							<p style="padding-right: 5px;">
								<small>
									${escapeHtml(
										message.message || ''
									)}
								</small>
							</p>
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
												<small>
													Message approved
												</small>
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
						</div>
						<div class="ai-sales-message-status">
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td colspan="1" align="left" valign="middle" style="padding: 0 0 5px;">
										<div class="bubble" style="background: var(--alert-blue); color: var(--max-blue);">
											${escapeHtml(
												message.direction || ''
											)}
										</div>
									</td>
									<td colspan="1" align="left" valign="middle" style="padding: 0 0 5px;">
										<div class="bubble" style="background: var(--alert-green); color: var(--agree-green);">
											${escapeHtml(
												message.status || 'DRAFT'
											)}
										</div>
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="1" align="left" valign="middle" style="padding: 0 0 5px;">
										${
											message.ai_generated
												? `
													<small>
														AI generated
													</small>
												`
												: ''
										}
									</td>
									<td colspan="1" align="left" valign="middle" style="padding: 0 0 5px;">
										${
											message.status === 'SENT'
												? `
													<small>
														Message sent
													</small>
												`
												: ''
										}
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="2" align="left" valign="middle">
										${
											message.sent_at
												? `
													<small>
														S:
														${escapeHtml(message.sent_at)}
													</small>
												`
												: ''
										}
									</td>
								</tr>
							</table>
						</div>
					</div>
				`).join('')}
			`;


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

	async function approveSalesMessage(
		salesMessageId,
		conversationId,
		salesCompanyId,
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
				salesCompanyId
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

						${
							contact.is_primary 
								? `<div class="bubble" style="background: var(--alert-green); color: var(--agree-green);">
									Primary contact
								</div>` 
								: ''
						}
					</div>
				`).join('')}
			`;

		} catch (error) {
			console.error('Error loading sales contacts:', error);

			contactsContainer.innerHTML = `
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

					const row = document.createElement('tr');
					row.className = 'users-row';

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

	searchCompanyField?.addEventListener(
		'keyup',
		fetchAndRenderCompanies
	);

	await fetchAndRenderCompanies();
};