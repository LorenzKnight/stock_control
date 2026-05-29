<?php
// public/pages/gdpr.php
$meta['title'] = (tr('gdpr_title') ?? 'GDPR') . ' | AllStockControl';
$meta['description'] = 'GDPR policy for AllStockControl.';

$emailInfo = 'info@allstockcontrol.com';
$emailSupport = 'support@allstockcontrol.com';
?>
<div class="container">
	<div class="data-container legal-page">
		<h1><?= htmlspecialchars(tr('gdpr_title') ?? 'GDPR') ?></h1>

		<?php if (($lang ?? 'en') === 'sv'): ?>

			<h2>1. Introduktion</h2>
			<p>
				Den här sidan beskriver hur <strong>AllStockControl</strong> behandlar personuppgifter i samband med vår webbplats och
				tjänsten (”Tjänsten”). Vi strävar efter att följa EU:s dataskyddsförordning (GDPR) när den är tillämplig och att
				tillämpa god praxis för integritet och säkerhet.
			</p>

			<h2>2. Personuppgiftsansvarig och kontakt</h2>
			<p>
				AllStockControl är personuppgiftsansvarig för personuppgifter som behandlas för vår egen webbplats, kontohantering,
				kundrelation och supportkommunikation.
			</p>
			<ul>
				<li><strong>Integritetskontakt:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Support:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
				<li><strong>Adress:</strong> (valfritt) kan lämnas på begäran eller när den är offentligt tillgänglig.</li>
			</ul>

			<h2>3. Roller: personuppgiftsansvarig och personuppgiftsbiträde (DPA)</h2>
			<p>
				När en kund använder AllStockControl behandlar vi normalt data <em>för kundens räkning</em>. I dessa fall är kunden
				personuppgiftsansvarig och AllStockControl är personuppgiftsbiträde. Personuppgiftsbiträdesavtal (DPA) kan
				tillhandahållas på begäran.
			</p>

			<h2>4. Vilka personuppgifter behandlar vi?</h2>

			<h3>A. Kontouppgifter</h3>
			<ul>
				<li>Namn och e-post</li>
				<li>Roll/behörighet</li>
				<li>Lösenord (lagras säkert, t.ex. hashas)</li>
				<li>Språkpreferenser</li>
			</ul>

			<h3>B. Användnings- och tekniska uppgifter</h3>
			<ul>
				<li>Aktivitetsloggar (t.ex. inloggningar och relevanta åtgärder i Tjänsten)</li>
				<li>Tekniska identifierare (t.ex. sessionsidentifierare)</li>
				<li>Enhets-/webbläsaruppgifter (t.ex. webbläsartyp, operativsystem)</li>
			</ul>

			<h3>C. Betalning/fakturering</h3>
			<ul>
				<li>Plan/abonnemang och status</li>
				<li>Betalningshistorik (hanteras normalt av betalningsleverantör)</li>
				<li>Minimala uppgifter för kvitto/faktura beroende på konfiguration</li>
			</ul>

			<h3>D. Kommunikation</h3>
			<ul>
				<li>Meddelanden du skickar (t.ex. via kontaktformulär)</li>
				<li>Supportmejl och svar</li>
			</ul>

			<h3>E. Data du lägger in i Tjänsten</h3>
			<p>
				AllStockControl är ett system för lager/inventariehantering. Användare kan lägga in produktinformation, lagerhändelser,
				bilder och intern affärsdata. Vi rekommenderar att undvika känsliga personuppgifter om det inte behövs.
			</p>

			<h2>5. Ändamål och rättslig grund (GDPR)</h2>
			<p>Vi behandlar personuppgifter endast när vi har en giltig rättslig grund.</p>

			<h3>A. För att tillhandahålla Tjänsten (avtal)</h3>
			<ul>
				<li>Skapa och administrera konto</li>
				<li>Tillhandahålla funktioner och lagra kunddata</li>
				<li>Ge support och hantera incidenter</li>
				<li>Säkerhet och förebyggande av missbruk</li>
			</ul>

			<h3>B. För att uppfylla rättsliga skyldigheter</h3>
			<ul>
				<li>Bokförings-/skattekrav och andra tillämpliga registerkrav (när relevant)</li>
			</ul>

			<h3>C. För att förbättra Tjänsten (berättigat intresse)</h3>
			<ul>
				<li>Analys av prestanda och stabilitet</li>
				<li>Felsökning och förbättring av säkerhet och användarupplevelse</li>
			</ul>

			<h3>D. Marknadsföring (samtycke eller berättigat intresse, när tillämpligt)</h3>
			<ul>
				<li>Utskick kopplade till Tjänsten (t.ex. viktiga uppdateringar)</li>
				<li>Nyheter/erbjudanden när det är tillämpligt och/eller du samtyckt</li>
			</ul>
			<p>Du kan när som helst avregistrera dig från marknadsföringsutskick genom att kontakta oss.</p>

			<h2>6. Cookies och liknande teknik</h2>
			<p>Vi kan använda cookies och liknande teknik för att:</p>
			<ul>
				<li>Upprätthålla sessioner och nödvändiga funktioner</li>
				<li>Komma ihåg preferenser (t.ex. språk)</li>
				<li>Mäta och förbättra prestanda (t.ex. analys om aktiverat)</li>
			</ul>
			<p>Du kan styra cookies via din webbläsare. Vissa cookies är nödvändiga för att Tjänsten ska fungera.</p>

			<h2>7. Delning av data (biträden/leverantörer)</h2>
			<p>
				Vi säljer inte personuppgifter. Vi kan dela data med betrodda leverantörer som hjälper oss att drifta Tjänsten, t.ex. hosting,
				e-post och betalningar. Leverantörer agerar som personuppgiftsbiträden och omfattas av avtal och lämpliga säkerhetskrav.
			</p>

			<h2>8. Internationella överföringar</h2>
			<p>
				Vi kan behandla data utanför EU/EES beroende på leverantörers plats. Vid internationella överföringar använder vi lämpliga
				skyddsåtgärder, exempelvis EU-kommissionens standardavtalsklausuler (SCC) när det krävs.
			</p>

			<h2>9. Säkerhet</h2>
			<p>Vi använder rimliga tekniska och organisatoriska åtgärder, såsom:</p>
			<ul>
				<li>Kryptering i transit (HTTPS/TLS)</li>
				<li>Åtkomstkontroller och behörighetsstyrning</li>
				<li>Övervakning och skydd mot missbruk</li>
				<li>Backuper och kontinuitetsåtgärder</li>
			</ul>
			<p>Inget system är 100% säkert, men vi arbetar aktivt för att minska risker.</p>

			<h2>10. Lagringstid</h2>
			<p>
				Vi sparar personuppgifter endast så länge det behövs för att tillhandahålla Tjänsten, uppfylla lagkrav och hantera tvister eller
				avtal. När data inte längre behövs raderas eller anonymiseras den på ett säkert sätt.
			</p>

			<h2>11. Dina rättigheter (EU/EES)</h2>
			<p>Om du befinner dig i EU/EES har du rätt att:</p>
			<ul>
				<li>få tillgång till dina personuppgifter</li>
				<li>begära rättelse</li>
				<li>begära radering (i vissa fall)</li>
				<li>begära begränsning</li>
				<li>begära dataportabilitet (i relevanta fall)</li>
				<li>invända mot behandling baserad på berättigat intresse</li>
				<li>återkalla samtycke (när tillämpligt)</li>
			</ul>
			<p>
				För att utöva dina rättigheter: <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a>.
				Vi kan be om rimlig identitetsverifiering för att skydda ditt konto.
			</p>
			<p>Du har även rätt att lämna klagomål till din dataskyddsmyndighet.</p>

			<h2>12. Minderåriga och ändringar</h2>
			<p>
				Tjänsten riktar sig inte till minderåriga. Kontakta oss om du tror att en minderårig lämnat personuppgifter, så hjälper vi till att radera dem.
				Vi kan uppdatera denna policy då och då. Den senaste versionen publiceras på denna sida.
			</p>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Senast uppdaterad: <?= date('Y-m-d') ?></p>

		<?php elseif (($lang ?? 'en') === 'es'): ?>

			<h2>1. Introducción</h2>
			<p>
				En <strong>AllStockControl</strong> (“AllStockControl”, “nosotros”) nos tomamos muy en serio la privacidad y la protección de datos.
				Esta política explica qué datos personales recopilamos, por qué los usamos, con quién los compartimos y qué derechos tienes.
			</p>
			<p>
				Esta política aplica cuando visitas nuestro sitio web, creas una cuenta o utilizas el servicio AllStockControl (el “Servicio”).
			</p>

			<h2>2. Responsable del tratamiento y contacto</h2>
			<p>
				AllStockControl actúa como responsable del tratamiento para los datos relacionados con nuestro sitio web, la gestión de cuentas
				y la comunicación de soporte. No es necesario que publiquemos un número de registro para explicar nuestras obligaciones de privacidad;
				si necesitas información adicional, contáctanos.
			</p>
			<ul>
				<li><strong>Contacto información:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Soporte:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
				<li><strong>Dirección:</strong> (opcional) disponible a solicitud o cuando sea públicamente aplicable.</li>
			</ul>

			<h2>3. Roles: responsable y encargado (DPA)</h2>
			<p>
				Cuando un cliente utiliza AllStockControl, normalmente tratamos datos <em>por cuenta del cliente</em>. En estos casos, el cliente
				es el responsable del tratamiento y AllStockControl actúa como encargado del tratamiento. Podemos proporcionar un Acuerdo de Encargado (DPA) a solicitud.
			</p>

			<h2>4. Qué datos personales tratamos</h2>

			<h3>A. Datos de cuenta</h3>
			<ul>
				<li>Nombre y email</li>
				<li>Rol/permisos</li>
				<li>Contraseña (almacenada de forma segura, por ejemplo hasheada)</li>
				<li>Preferencias de idioma</li>
			</ul>

			<h3>B. Datos de uso del Servicio</h3>
			<ul>
				<li>Registros de actividad (por ejemplo, fecha/hora de acceso y acciones relevantes dentro del Servicio)</li>
				<li>Identificadores técnicos (por ejemplo, identificadores de sesión)</li>
				<li>Datos de dispositivo/navegador (por ejemplo, tipo de navegador, sistema operativo)</li>
			</ul>

			<h3>C. Datos de facturación</h3>
			<ul>
				<li>Plan contratado y estado de suscripción</li>
				<li>Historial de pagos (normalmente gestionado por un proveedor de pago)</li>
				<li>Datos mínimos necesarios para facturas/recibos según configuración</li>
			</ul>

			<h3>D. Comunicaciones</h3>
			<ul>
				<li>Mensajes que nos envías (por ejemplo, a través del formulario de contacto)</li>
				<li>Emails de soporte y respuestas</li>
			</ul>

			<h3>E. Datos que tú subes al Servicio</h3>
			<p>
				AllStockControl es un sistema de gestión de inventario. Los usuarios pueden cargar información relacionada con productos,
				movimientos de inventario, imágenes y datos internos de su negocio. Recomendamos evitar subir datos personales sensibles si no es necesario.
			</p>

			<h2>5. Para qué usamos tus datos y base legal (GDPR)</h2>
			<p>Usamos tus datos personales solo cuando tenemos una base legal válida.</p>

			<h3>A. Para prestar el Servicio (Ejecución de contrato)</h3>
			<ul>
				<li>Crear y administrar tu cuenta</li>
				<li>Proveer funcionalidades del Servicio</li>
				<li>Dar soporte y resolver incidencias</li>
				<li>Mantener la seguridad y prevenir fraude/abuso</li>
			</ul>

			<h3>B. Para cumplir obligaciones legales (Obligación legal)</h3>
			<ul>
				<li>Requisitos contables/fiscales y registros aplicables (si corresponde)</li>
			</ul>

			<h3>C. Para mejorar el Servicio (Interés legítimo)</h3>
			<ul>
				<li>Analizar rendimiento y estabilidad</li>
				<li>Diagnosticar errores y mejorar seguridad y experiencia</li>
			</ul>

			<h3>D. Marketing (Consentimiento o interés legítimo, según el caso)</h3>
			<ul>
				<li>Enviar comunicaciones relacionadas con el Servicio (por ejemplo, cambios importantes, avisos técnicos)</li>
				<li>Enviar novedades o promociones cuando corresponda y/o cuando hayas dado tu consentimiento (si aplica)</li>
			</ul>
			<p>Puedes darte de baja de comunicaciones comerciales en cualquier momento escribiéndonos.</p>

			<h2>6. Cookies y tecnologías similares</h2>
			<p>Podemos usar cookies y tecnologías similares para:</p>
			<ul>
				<li>Mantener sesiones y funcionalidades esenciales</li>
				<li>Recordar preferencias (como idioma)</li>
				<li>Medir y mejorar rendimiento (por ejemplo, analítica si está activada)</li>
			</ul>
			<p>Puedes controlar cookies desde la configuración de tu navegador. Algunas cookies son necesarias para que el Servicio funcione correctamente.</p>

			<h2>7. Con quién compartimos datos (encargados del tratamiento)</h2>
			<p>No vendemos datos personales. Solo compartimos datos cuando es necesario para operar el Servicio, por ejemplo con:</p>
			<ul>
				<li>Proveedores de hosting e infraestructura</li>
				<li>Proveedores de email (verificación, notificaciones y soporte)</li>
				<li>Procesadores de pago (si usas suscripciones/pagos)</li>
				<li>Herramientas de analítica/telemetría (si las usas)</li>
			</ul>
			<p>Estos proveedores actúan como encargados del tratamiento y están sujetos a acuerdos y medidas de seguridad apropiadas.</p>

			<h2>8. Transferencias internacionales</h2>
			<p>
				Podemos procesar datos en países fuera del EEE dependiendo de la ubicación de nuestros proveedores. Cuando exista una transferencia internacional,
				aplicamos salvaguardas apropiadas, como Cláusulas Contractuales Tipo (SCC) u otras medidas permitidas por el GDPR, cuando sea necesario.
			</p>

			<h2>9. Seguridad</h2>
			<p>Aplicamos medidas técnicas y organizativas razonables para proteger tus datos, como:</p>
			<ul>
				<li>Cifrado en tránsito (HTTPS/TLS)</li>
				<li>Controles de acceso y privilegios</li>
				<li>Monitoreo y protección contra abuso</li>
				<li>Copias de seguridad y medidas de continuidad</li>
			</ul>
			<p>Ningún sistema es 100% infalible, pero trabajamos para reducir riesgos y responder ante incidentes.</p>

			<h2>10. Conservación de datos</h2>
			<p>
				Conservamos los datos solo durante el tiempo necesario para prestar el Servicio, cumplir obligaciones legales, resolver disputas y hacer cumplir acuerdos.
				Cuando ya no sea necesario, eliminamos o anonimamos los datos de forma segura.
			</p>

			<h2>11. Tus derechos (usuarios en la UE/EEE)</h2>
			<p>Si estás en la UE/EEE, tienes derecho a:</p>
			<ul>
				<li>Acceso a tus datos</li>
				<li>Rectificación de datos incorrectos</li>
				<li>Supresión (“derecho al olvido”) en ciertos casos</li>
				<li>Limitación del tratamiento</li>
				<li>Portabilidad de los datos</li>
				<li>Oposición al tratamiento basado en interés legítimo</li>
				<li>Retirar tu consentimiento en cualquier momento (cuando aplique)</li>
			</ul>
			<p>
				Para ejercer tus derechos, escribe a <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a>.
				Podemos solicitar verificación razonable de identidad para proteger tu cuenta.
			</p>
			<p>También tienes derecho a presentar una reclamación ante tu autoridad de control de protección de datos.</p>

			<h2>12. Datos de menores y cambios a esta política</h2>
			<p>
				El Servicio no está dirigido a menores. Si crees que un menor nos ha proporcionado datos personales, contáctanos para eliminarlos.
				Podemos actualizar esta política ocasionalmente. Publicaremos la versión actualizada en esta página e indicaremos la fecha de “última actualización”.
			</p>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Última actualización: <?= date('Y-m-d') ?></p>

		<?php else: /* en */ ?>

			<h2>1. Introduction</h2>
			<p>
				At <strong>AllStockControl</strong> (“AllStockControl”, “we”), we take privacy and data protection seriously.
				This policy explains what personal data we collect, why we use it, who we share it with, and what rights you have.
			</p>
			<p>
				This policy applies when you visit our website, create an account, or use AllStockControl (the “Service”).
			</p>

			<h2>2. Data controller and contact</h2>
			<p>
				AllStockControl is the data controller for personal data related to our website, account administration and support communications.
				If you need additional company information, please contact us via the email addresses below.
			</p>
			<ul>
				<li><strong>Information contact:</strong> <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a></li>
				<li><strong>Support:</strong> <a href="mailto:<?= htmlspecialchars($emailSupport) ?>"><?= htmlspecialchars($emailSupport) ?></a></li>
				<li><strong>Address:</strong> (optional) available upon request or when publicly available.</li>
			</ul>

			<h2>3. Roles: controller and processor (DPA)</h2>
			<p>
				When a customer uses AllStockControl, we typically process data <em>on the customer’s behalf</em>.
				In those cases, the customer is the controller and AllStockControl acts as a processor. A Data Processing Agreement (DPA)
				can be provided upon request.
			</p>

			<h2>4. Personal data we process</h2>

			<h3>A. Account data</h3>
			<ul>
				<li>Name and email</li>
				<li>Role/permissions</li>
				<li>Password (stored securely, e.g., hashed)</li>
				<li>Language preferences</li>
			</ul>

			<h3>B. Service usage data</h3>
			<ul>
				<li>Activity records (e.g., login time and relevant actions within the Service)</li>
				<li>Technical identifiers (e.g., session identifiers)</li>
				<li>Device/browser data (e.g., browser type, operating system)</li>
			</ul>

			<h3>C. Billing data</h3>
			<ul>
				<li>Subscription plan and status</li>
				<li>Payment history (typically handled by a payment provider)</li>
				<li>Minimum data needed for invoices/receipts depending on configuration</li>
			</ul>

			<h3>D. Communications</h3>
			<ul>
				<li>Messages you send us (e.g., through the contact form)</li>
				<li>Support emails and replies</li>
			</ul>

			<h3>E. Data you upload</h3>
			<p>
				AllStockControl is an inventory management system. Users may upload product information, stock movements, images and internal business data.
				We recommend avoiding sensitive personal data unless it is necessary.
			</p>

			<h2>5. Purposes and legal bases (GDPR)</h2>
			<p>We process personal data only when we have a valid legal basis.</p>

			<h3>A. Provide the Service (contract)</h3>
			<ul>
				<li>Create and manage your account</li>
				<li>Provide Service features</li>
				<li>Support and incident handling</li>
				<li>Security and fraud/abuse prevention</li>
			</ul>

			<h3>B. Legal obligations</h3>
			<ul>
				<li>Accounting/tax requirements and other applicable record keeping (where relevant)</li>
			</ul>

			<h3>C. Improve the Service (legitimate interests)</h3>
			<ul>
				<li>Performance and stability analysis</li>
				<li>Troubleshooting and improving security and user experience</li>
			</ul>

			<h3>D. Marketing (consent or legitimate interests, where applicable)</h3>
			<ul>
				<li>Service-related communications (e.g., important updates)</li>
				<li>News/promotions where applicable and/or with your consent</li>
			</ul>
			<p>You can opt out of marketing emails at any time by contacting us.</p>

			<h2>6. Cookies and similar technologies</h2>
			<p>We may use cookies and similar technologies to:</p>
			<ul>
				<li>Maintain sessions and essential functionality</li>
				<li>Remember preferences (such as language)</li>
				<li>Measure and improve performance (e.g., analytics if enabled)</li>
			</ul>
			<p>You can control cookies through your browser settings. Some cookies are necessary for the Service to function properly.</p>

			<h2>7. Sharing with vendors (processors)</h2>
			<p>
				We do not sell personal data. We may share data with trusted vendors that help us operate the Service (e.g., hosting, email, payments, analytics).
				Vendors act as processors and are subject to confidentiality and appropriate security obligations.
			</p>

			<h2>8. International transfers</h2>
			<p>
				We may process data outside the EEA depending on where our vendors are located. Where required, we rely on appropriate safeguards such as
				Standard Contractual Clauses (SCCs) or equivalent measures.
			</p>

			<h2>9. Security</h2>
			<p>We apply reasonable technical and organizational measures, such as:</p>
			<ul>
				<li>Encryption in transit (HTTPS/TLS)</li>
				<li>Access controls and least-privilege permissions</li>
				<li>Monitoring and abuse prevention</li>
				<li>Backups and continuity measures</li>
			</ul>
			<p>No system is 100% secure, but we work continuously to reduce risks.</p>

			<h2>10. Data retention</h2>
			<p>
				We keep personal data only as long as necessary to provide the Service, comply with legal obligations, resolve disputes and enforce agreements.
				When no longer needed, we securely delete or anonymize data.
			</p>

			<h2>11. Your rights (EU/EEA)</h2>
			<p>If you are in the EU/EEA, you have the right to:</p>
			<ul>
				<li>Access your personal data</li>
				<li>Correct inaccurate data</li>
				<li>Request deletion (in certain cases)</li>
				<li>Restrict processing</li>
				<li>Data portability</li>
				<li>Object to processing based on legitimate interests</li>
				<li>Withdraw consent at any time (where applicable)</li>
			</ul>
			<p>
				To exercise your rights, contact <a href="mailto:<?= htmlspecialchars($emailInfo) ?>"><?= htmlspecialchars($emailInfo) ?></a>.
				We may request reasonable identity verification to protect your account.
			</p>
			<p>You also have the right to lodge a complaint with your data protection authority.</p>

			<h2>12. Children and policy updates</h2>
			<p>
				The Service is not intended for children. If you believe a child has provided personal data, contact us and we will help delete it.
				We may update this policy from time to time. The latest version will be published on this page.
			</p>

			<hr>
			<p style="font-size: 0.9em; opacity: 0.8;">Last updated: <?= date('Y-m-d') ?></p>

		<?php endif; ?>
	</div>
</div>