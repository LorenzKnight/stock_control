<?php
// public/pages/terms.php
$meta['title'] = 'Terms & Conditions | AllStockControl';
$meta['description'] = 'Terms and conditions for using AllStockControl.';

$emailInfo = 'info@allstockcontrol.com';
$emailSupport = 'support@allstockcontrol.com';
$companyDisplay = 'AllStockControl';
?>
<div class="container">
	<div class="data-container legal-page">
		<h1><?= htmlspecialchars(tr('terms_title') ?? 'Terms & Conditions') ?></h1>

		<?php if (($lang ?? 'en') === 'sv'): ?>

			<h2>1. Inledning</h2>
			<p>
				Dessa villkor (“Villkoren”) reglerar din användning av webbplatsen och tjänsten <strong><?= htmlspecialchars($companyDisplay) ?></strong>
				(“Tjänsten”). Genom att skapa ett konto, använda Tjänsten eller besöka webbplatsen godkänner du Villkoren.
				Om du inte accepterar Villkoren ska du inte använda Tjänsten.
			</p>

			<h2>2. Konton och behörigheter</h2>
			<ul>
				<li>Du ansvarar för att informationen du lämnar är korrekt och uppdaterad.</li>
				<li>Du ansvarar för att skydda dina inloggningsuppgifter och för all aktivitet som sker via ditt konto.</li>
				<li>Du får inte dela konto på ett sätt som kringgår licens-/användarbegränsningar.</li>
			</ul>

			<h2>3. Acceptabel användning</h2>
			<p>Du får inte använda Tjänsten för att:</p>
			<ul>
				<li>bryta mot lagar eller tredje parts rättigheter</li>
				<li>skicka skadlig kod, försöka hacka, överbelasta eller kringgå säkerhet</li>
				<li>skrapa, kopiera eller extrahera data på ett otillåtet sätt</li>
				<li>använda Tjänsten för spam, bedrägerier eller vilseledande aktiviteter</li>
			</ul>
			<p>Vi kan tillfälligt eller permanent stänga av åtkomst vid misstanke om missbruk eller säkerhetsrisk.</p>

			<h2>4. Kunddata och ansvar för innehåll</h2>
			<ul>
				<li>Du äger och ansvarar för den data (“Kunddata”) som du laddar upp eller skapar i Tjänsten.</li>
				<li>Du garanterar att du har rätt att behandla Kunddata och att den inte bryter mot lag eller tredje parts rättigheter.</li>
				<li>Vi ansvarar inte för Kunddata, dess riktighet eller laglighet.</li>
			</ul>

			<h2>5. Sekretess och dataskydd</h2>
			<p>
				Vår behandling av personuppgifter beskrivs i vår integritets-/GDPR-policy. Där det är tillämpligt kan DPA tillhandahållas på begäran.
			</p>

			<h2>6. Abonnemang, betalning och skatt</h2>
			<ul>
				<li>Betalplaner, priser och funktioner kan ändras över tid. Vi informerar rimligt i förväg när det är möjligt.</li>
				<li>Avgifter är normalt förskottsbetalda och återbetalas inte i efterhand, förutom där tvingande lag kräver annat.</li>
				<li>Du ansvarar för eventuella skatter, avgifter och andra kostnader kopplade till köpet.</li>
			</ul>

			<h2>7. Avslut, uppsägning och radering</h2>
			<ul>
				<li>Du kan säga upp ditt abonnemang enligt de metoder som finns i Tjänsten eller genom att kontakta support.</li>
				<li>Vi kan stänga av eller avsluta konton vid väsentligt avtalsbrott, missbruk eller säkerhetsrisk.</li>
				<li>Efter uppsägning kan åtkomst till Tjänsten upphöra och data kan raderas enligt vår policy och tillämplig lag.</li>
			</ul>

			<h2>8. Tillgänglighet och ändringar</h2>
			<p>
				Tjänsten tillhandahålls i befintligt skick. Vi strävar efter hög tillgänglighet men garanterar inte oavbruten drift.
				Vi kan ändra, uppdatera eller avveckla funktioner (helt eller delvis) för att förbättra säkerhet, prestanda eller uppfylla krav.
			</p>

			<h2>9. Immateriella rättigheter</h2>
			<p>
				Tjänsten, dess design, kod, varumärken och innehåll (förutom Kunddata) ägs av <?= htmlspecialchars($companyDisplay) ?> eller dess licensgivare.
				Du får en begränsad, icke-exklusiv, icke-överlåtbar rätt att använda Tjänsten enligt Villkoren.
			</p>

			<h2>10. Ansvarsbegränsning</h2>
			<p>
				I den utsträckning lagen tillåter ansvarar <?= htmlspecialchars($companyDisplay) ?> inte för indirekta skador,
				utebliven vinst, förlorad data, avbrott i verksamhet eller följdskador.
				Vårt totala ansvar är begränsat till det belopp du betalat för Tjänsten under de senaste tre (3) månaderna före händelsen
				som gav upphov till kravet, eller om inget betalats, ett begränsat belopp.
			</p>

			<h2>11. Skadeslöshet</h2>
			<p>
				Du åtar dig att hålla <?= htmlspecialchars($companyDisplay) ?> skadeslöst från krav som uppstår på grund av din användning av Tjänsten,
				din Kunddata eller brott mot Villkoren eller lag.
			</p>

			<h2>12. Tillämplig lag och tvist</h2>
			<p>
				Dessa Villkor ska tolkas enligt tillämplig lag. Tvister ska i första hand lösas genom dialog.
				Om tvist inte kan lösas kan den prövas av behörig domstol enligt tillämpliga regler.
			</p>

			<h2>13. Kontakt</h2>
			<ul>
				<li><strong>Info:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Support:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
			</ul>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Senast uppdaterad: <?= date('Y-m-d') ?></p>

		<?php elseif (($lang ?? 'en') === 'es'): ?>

			<h2>1. Introducción</h2>
			<p>
				Estos términos y condiciones (“Términos”) regulan el uso del sitio web y del servicio <strong><?= htmlspecialchars($companyDisplay) ?></strong>
				(el “Servicio”). Al crear una cuenta, acceder o utilizar el Servicio, aceptas estos Términos. Si no estás de acuerdo,
				no utilices el Servicio.
			</p>

			<h2>2. Cuentas y seguridad</h2>
			<ul>
				<li>Eres responsable de que la información proporcionada sea veraz y esté actualizada.</li>
				<li>Eres responsable de mantener la confidencialidad de tus credenciales y de toda actividad realizada desde tu cuenta.</li>
				<li>No debes compartir cuentas o acceder de forma que eluda límites de usuarios/licencias.</li>
			</ul>

			<h2>3. Uso aceptable</h2>
			<p>No puedes usar el Servicio para:</p>
			<ul>
				<li>incumplir leyes o derechos de terceros</li>
				<li>introducir malware, intentar vulnerar seguridad, saturar o interferir el Servicio</li>
				<li>extraer datos de forma no autorizada (scraping), copiar o reproducir el Servicio indebidamente</li>
				<li>realizar spam, fraude o actividades engañosas</li>
			</ul>
			<p>Podemos suspender o terminar el acceso si detectamos abuso o riesgo de seguridad.</p>

			<h2>4. Datos del cliente y responsabilidad por contenido</h2>
			<ul>
				<li>Tú eres el propietario y responsable de los datos que subes o generas en el Servicio (“Datos del Cliente”).</li>
				<li>Garantizas que tienes derechos y base legal para tratar los Datos del Cliente.</li>
				<li>No somos responsables de la legalidad, exactitud o integridad de los Datos del Cliente.</li>
			</ul>

			<h2>5. Privacidad y protección de datos</h2>
			<p>
				Nuestro tratamiento de datos personales se describe en nuestra Política de Privacidad / GDPR. Cuando aplique, un DPA puede proporcionarse a solicitud.
			</p>

			<h2>6. Suscripción, pagos e impuestos</h2>
			<ul>
				<li>Los planes, precios y funcionalidades pueden cambiar con el tiempo. Cuando sea posible, avisaremos con antelación razonable.</li>
				<li>Los pagos suelen ser por adelantado y no son reembolsables, salvo que la ley obligue lo contrario.</li>
				<li>Eres responsable de impuestos, tasas y cargos aplicables.</li>
			</ul>

			<h2>7. Cancelación, suspensión y eliminación</h2>
			<ul>
				<li>Puedes cancelar según las opciones disponibles en el Servicio o contactando a soporte.</li>
				<li>Podemos suspender o cerrar cuentas por incumplimiento grave, abuso o riesgo de seguridad.</li>
				<li>Tras la cancelación, el acceso puede terminar y los datos pueden eliminarse conforme a nuestra política y la ley aplicable.</li>
			</ul>

			<h2>8. Disponibilidad y cambios del Servicio</h2>
			<p>
				El Servicio se ofrece “tal cual”. Nos esforzamos por mantener alta disponibilidad, pero no garantizamos funcionamiento ininterrumpido.
				Podemos modificar, actualizar o descontinuar funciones (total o parcialmente) para mejorar seguridad, rendimiento o cumplir requisitos.
			</p>

			<h2>9. Propiedad intelectual</h2>
			<p>
				El Servicio, su diseño, código, marcas y contenido (excepto los Datos del Cliente) pertenecen a <?= htmlspecialchars($companyDisplay) ?> o a sus licenciantes.
				Se te concede una licencia limitada, no exclusiva y no transferible para usar el Servicio conforme a estos Términos.
			</p>

			<h2>10. Limitación de responsabilidad</h2>
			<p>
				En la máxima medida permitida por la ley, <?= htmlspecialchars($companyDisplay) ?> no será responsable por daños indirectos,
				pérdida de beneficios, pérdida de datos, interrupción del negocio o daños consecuentes.
				Nuestra responsabilidad total se limita al importe pagado por el Servicio durante los últimos tres (3) meses anteriores al hecho
				que originó la reclamación, o si no hubo pagos, a un importe limitado.
			</p>

			<h2>11. Indemnización</h2>
			<p>
				Aceptas indemnizar y mantener indemne a <?= htmlspecialchars($companyDisplay) ?> frente a reclamaciones derivadas de tu uso del Servicio,
				tus Datos del Cliente o el incumplimiento de estos Términos o de la ley.
			</p>

			<h2>12. Ley aplicable y resolución de disputas</h2>
			<p>
				Estos Términos se interpretarán conforme a la legislación aplicable. Cualquier disputa se intentará resolver primero de forma amistosa.
				Si no se logra un acuerdo, la disputa podrá someterse a los tribunales competentes según las normas aplicables.
			</p>

			<h2>13. Contacto</h2>
			<ul>
				<li><strong>Info:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Soporte:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
			</ul>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Última actualización: <?= date('Y-m-d') ?></p>

		<?php else: /* en */ ?>

			<h2>1. Introduction</h2>
			<p>
				These Terms and Conditions (“Terms”) govern your use of the website and the <strong><?= htmlspecialchars($companyDisplay) ?></strong> service
				(the “Service”). By creating an account, accessing, or using the Service, you agree to these Terms. If you do not agree, do not use the Service.
			</p>

			<h2>2. Accounts and security</h2>
			<ul>
				<li>You are responsible for keeping your account information accurate and up to date.</li>
				<li>You are responsible for safeguarding your credentials and for all activity under your account.</li>
				<li>You may not share accounts or access the Service in a way that circumvents user/license limits.</li>
			</ul>

			<h2>3. Acceptable use</h2>
			<p>You may not use the Service to:</p>
			<ul>
				<li>violate laws or third-party rights</li>
				<li>upload malware, attempt to breach security, overload, disrupt, or interfere with the Service</li>
				<li>scrape, copy, reverse engineer, or extract data in an unauthorized manner</li>
				<li>send spam, engage in fraud, or misleading activities</li>
			</ul>
			<p>We may suspend or terminate access if we detect abuse or a security risk.</p>

			<h2>4. Customer data and content responsibility</h2>
			<ul>
				<li>You own and are responsible for the data you upload or generate in the Service (“Customer Data”).</li>
				<li>You represent that you have the rights and legal basis to process Customer Data.</li>
				<li>We are not responsible for the legality, accuracy, or completeness of Customer Data.</li>
			</ul>

			<h2>5. Privacy and data protection</h2>
			<p>
				Our processing of personal data is described in our Privacy / GDPR policy. Where applicable, a Data Processing Agreement (DPA) can be provided upon request.
			</p>

			<h2>6. Subscriptions, payments and taxes</h2>
			<ul>
				<li>Plans, pricing, and features may change over time. Where possible, we will provide reasonable advance notice.</li>
				<li>Fees are typically prepaid and non-refundable, except where required by mandatory law.</li>
				<li>You are responsible for applicable taxes, duties, and related charges.</li>
			</ul>

			<h2>7. Cancellation, suspension, and deletion</h2>
			<ul>
				<li>You may cancel according to the options provided in the Service or by contacting support.</li>
				<li>We may suspend or terminate accounts for material breach, abuse, or security risk.</li>
				<li>After cancellation, access may end and data may be deleted according to our policy and applicable law.</li>
			</ul>

			<h2>8. Service availability and changes</h2>
			<p>
				The Service is provided “as is.” We strive for high availability but do not guarantee uninterrupted operation.
				We may modify, update, or discontinue features (in whole or in part) to improve security, performance, or compliance.
			</p>

			<h2>9. Intellectual property</h2>
			<p>
				The Service, its design, code, trademarks, and content (excluding Customer Data) are owned by <?= htmlspecialchars($companyDisplay) ?> or its licensors.
				You are granted a limited, non-exclusive, non-transferable license to use the Service in accordance with these Terms.
			</p>

			<h2>10. Limitation of liability</h2>
			<p>
				To the maximum extent permitted by law, <?= htmlspecialchars($companyDisplay) ?> will not be liable for indirect, incidental, special,
				consequential damages, lost profits, loss of data, or business interruption.
				Our total liability is limited to the amount you paid for the Service in the three (3) months prior to the event giving rise to the claim,
				or if no fees were paid, a limited amount.
			</p>

			<h2>11. Indemnification</h2>
			<p>
				You agree to indemnify and hold harmless <?= htmlspecialchars($companyDisplay) ?> from claims arising out of your use of the Service,
				your Customer Data, or your breach of these Terms or applicable law.
			</p>

			<h2>12. Governing law and dispute resolution</h2>
			<p>
				These Terms will be interpreted under applicable law. Disputes should first be attempted to be resolved amicably.
				If unresolved, disputes may be brought before competent courts as permitted by applicable rules.
			</p>

			<h2>13. Contact</h2>
			<ul>
				<li><strong>Info:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Support:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
			</ul>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Last updated: <?= date('Y-m-d') ?></p>

		<?php endif; ?>
	</div>
</div>