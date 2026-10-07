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

	const salesContactsCache = new Map();

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
								company.description ||
								'No description available.'
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
												stroke-width="2"
												stroke-linecap="round"
												stroke-linejoin="round"
											>
												<rect x="3" y="5" width="18" height="16" rx="2"></rect>
												<path d="M8 3v4"></path>
												<path d="M16 3v4"></path>
												<path d="M3 10h18"></path>
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

				${data.data.map(message => `
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
			const contacts =
				await getSalesContacts(
					salesCompanyId
				);


			if (contacts.length === 0) {
				contactsContainer.innerHTML = `
					<h3>Contacts</h3>
					<p>No contacts found.</p>
				`;

				return;
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
							<circle cx="12" cy="7.5" r="3"></circle>
							<path d="M6 20v-1c0-3.1 2.7-5.5 6-5.5s6 2.4 6 5.5v1"></path>
							<path d="M6 20h12"></path>
						</svg>
						Contacts
					</span>
				</h3>

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
									<rect x="3" y="5" width="18" height="14" rx="2"></rect>
									<path d="M3 7l9 6 9-6"></path>
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
									<path d="M5 4h4l2 5-3 2a15 15 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2C10.3 21 3 13.7 3 6a2 2 0 0 1 2-2z"></path>
								</svg>
								${escapeHtml(
									contact.phone || '-'
								)}
							</span>
						</p>

						${
							contact.is_primary
								? `
									<div class="bubble bubble-success">
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
												<path d="M8.5 12.5l2.2 2.2 4.8-5"></path>
											</svg>
											Primary contact
										</span>
									</div>
								`
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

	searchCompanyField?.addEventListener(
		'keyup',
		fetchAndRenderCompanies
	);

	await fetchAndRenderCompanies();
};