CREATE TABLE IF NOT EXISTS users (
	user_id SERIAL PRIMARY KEY,
	parent_user INTEGER NULL,
	name VARCHAR(255) NULL,
	surname VARCHAR(255) NULL,
	email VARCHAR(255) NULL,
	country_code VARCHAR(10) NULL,
	phone VARCHAR(20) NULL,
	username VARCHAR(255) NULL,
	password VARCHAR(255) NULL,
	image VARCHAR(255) NULL,
	verified INTEGER NULL,
	birthday TIMESTAMP NULL,
	signup_date TIMESTAMP NULL,
	rank INTEGER NULL,
	company_id INTEGER NULL,
	package_id INTEGER NULL,
	status INTEGER NULL,
	status_by_admin INTEGER NULL
);

-- INSERT INTO users (parent_user, name, surname, email, phone, username, password, image, verified, birthday, signup_date, company_id, package_id, status)
-- VALUES (null, 'Lorenz', 'Knight', 'lorenz.knight@gmail.com', 763199480, 'lorenz_knight', 123456, 'profile_user_1_1742243935.jpg', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', null, 3, 1),
-- 	(1, 'Joel', 'Knight', 'joel.knight@gmail.com', null, 'joel_knight', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Shael', 'Knight', 'shael.knight@gmail.com', null, 'shael_knight', 123456, 'perfil.png', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'John', 'Doe', 'john.doe@gmail.com', null, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Lorenzo', 'Knight', 'lorenzo.knight@gmail.com', 763199480, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1);

CREATE TABLE IF NOT EXISTS user_tokens (
    token_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    token TEXT NOT NULL,
	refresh_token TEXT NULL,
    status VARCHAR(20) DEFAULT 'active',  -- active | revoked | expired
	ip_address VARCHAR(45) NULL,
	device_type VARCHAR(100) NULL,
	device_name VARCHAR(150) NULL,
	location VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    expires_at TIMESTAMP DEFAULT (NOW() + INTERVAL '1 day'),  -- access token
	refresh_expires_at TIMESTAMP DEFAULT (NOW() + INTERVAL '30 days')
);

CREATE TABLE IF NOT EXISTS packages (
	package_id SERIAL PRIMARY KEY,
	package_name VARCHAR(255) NULL,
	package_image VARCHAR(255) NULL,
	package_description TEXT NULL,
	package_price INTEGER NULL,
	members_limit INTEGER NULL, -- Límite de miembros
	admins_limit INTEGER NULL, -- Límite de administradores
	branch_affiliate_limit INTEGER NULL, -- Límite de sucursales afiliadas
	products_limit INTEGER NULL, -- Límite de productos
	package_duration INTEGER NULL, -- Duración en dias
	package_status INTEGER NULL, -- 0: Inactivo, 1: Activo
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO packages (
--   package_name, package_image, package_description, package_price, members_limit, admins_limit, branch_affiliate_limit, products_limit, package_duration, package_status
-- ) VALUES 
-- ('Try Pack', 'pack-upgrade-2.png',
--  'Perfect to explore the system with no commitment. Includes 1 user, limited access to essential features, and 1 affiliate branch. Great for solo entrepreneurs.', 
--  NULL, 0, 0, 1, 50, 30, 1),

-- ('Starter', 'pack-upgrade-2.png',
--  'Ideal for freelancers and very small businesses starting with inventory control.', 
--  15, 1, 1, 1, 100, NULL, 1),

-- ('Basic', 'pack-upgrade-2.png',
--  'Perfect for small shops that need better control of products and stock movements.', 
--  25, 5, 1, 1, 250, NULL, 1),

-- ('Business', 'pack-upgrade-2.png',
--  'Designed for growing businesses managing inventory across multiple locations.', 
--  40, 10, 2, 2, 500, NULL, 1),

-- ('Growth', 'pack-upgrade-2.png',
--  'For businesses scaling operations with multiple locations and teams.', 
--  60, 20, 3, 3, 1000, NULL, 1),

-- ('Scale', 'pack-upgrade-2.png',
--  'Built for large operations that require performance, control, and scalability.', 
--  85, 35, 4, 4, 2500, NULL, 1),

-- ('Enterprise', 'pack-upgrade-2.png',
--  'Custom inventory solution for large organizations. Contact us for a tailored plan.', 
--  200, 40, 5, 5, NULL, NULL, 1);

CREATE TABLE IF NOT EXISTS subscriptions (
	subsc_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	package_id INTEGER NULL,
	stripe_subscription_id VARCHAR(100) NULL,
	estimated_cost INTEGER NULL,
	subscription_date TIMESTAMP NULL,
	expiration_date TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS activity_history (
	history_id SERIAL PRIMARY KEY,
	user_id INT NOT NULL,
	action_type VARCHAR(50) NOT NULL, -- Ej: 'subscription_upgrade', 'user_create', 'company_update', etc.
	action_description TEXT,          -- Texto libre con el detalle de lo que ocurrió
	related_table VARCHAR(50),        -- Opcional: nombre de la tabla relacionada (ej. 'subscriptions', 'users')
	related_id INT,                   -- Opcional: ID del registro afectado
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
	company_id SERIAL PRIMARY KEY,
	user_id INT NOT NULL,
	company_type INTEGER NULL,
	company_name VARCHAR(255) NULL,
	organization_no INTEGER NULL,
	company_address VARCHAR(255) NULL,
	country_code VARCHAR(10) NULL,
	company_phone VARCHAR(20) NULL,
	company_logo VARCHAR(255) NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO companies (user_id, company_type, company_name, organization_no, company_address, company_phone, company_logo, created_at)
-- VALUES (1, null, 'My Company', 1234123, 'Siriusgatan 102', 'SE|+46', '+46 763199480', 'logo_user_1_1742063586.png', '2025-03-14 15:06:38.783');

CREATE TABLE IF NOT EXISTS product_type (
	product_type_id SERIAL PRIMARY KEY,
	product_type_name VARCHAR(255) NULL,
	company_id INTEGER NULL,
	user_id INT NOT NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
	product_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	create_by INT NOT NULL,
	sale_unit_type INTEGER NULL,
	units_per_pack INTEGER NULL,
	product_image VARCHAR(255) NULL,
	product_name VARCHAR(255) NULL,
	hs_code VARCHAR(50) NULL,
	product_type INTEGER NULL,
	product_mark INTEGER NULL,
	product_model INTEGER NULL,
	product_sub_model INTEGER NULL,
	product_year INTEGER NULL,
	description TEXT NULL,
	currency VARCHAR(10) NULL,
	price INTEGER NULL,
	purpose INTEGER NULL,
	quantity INTEGER NULL,
	min_quantity INTEGER NULL,
	weight_per_unit NUMERIC(10,3) NULL,
	total_weight NUMERIC(10,3) NULL,
	status INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS category (
	user_id INT NOT NULL,
	category_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	create_by INTEGER NULL,
	category_name VARCHAR(255) NULL,
	cat_parent_sub INTEGER NULL,
	sub_parent INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
	customer_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	customer_name VARCHAR(255) NULL,
	customer_surname VARCHAR(255) NULL,
	customer_image VARCHAR(255) NULL,
	customer_email VARCHAR(255) NULL,
	cu_country_code VARCHAR(10) NULL,
	customer_phone VARCHAR(20) NULL,
	customer_address VARCHAR(255) NULL,
	customer_birthday TIMESTAMP NULL,
	customer_document_type INTEGER NULL,
	customer_document_no VARCHAR(20) NULL,
	marital_status INTEGER NULL,
	references_1 VARCHAR(255) NULL,
	r1_country_code VARCHAR(10) NULL,
	references_1_phone VARCHAR(20) NULL,
	references_2 VARCHAR(255) NULL,
	r2_country_code VARCHAR(10) NULL,
	references_2_phone VARCHAR(20) NULL,
	customer_type INTEGER NULL,
	customer_status INTEGER NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers_doc (
	doc_id SERIAL PRIMARY KEY,
	customer_id INTEGER NULL,
	document_img VARCHAR(20) NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sales (
	sales_id SERIAL PRIMARY KEY,
	ord_no INTEGER NULL,
	customer_id INTEGER NULL,
	company_id INTEGER NULL,
	currency VARCHAR(10) NULL,
	price_sum NUMERIC(10,2) NULL,
	Initial NUMERIC(10,2) NULL,
	delivery_date TIMESTAMP NULL,
	remaining NUMERIC(10,2) NULL,
	interest INTEGER NULL,
	installments_month INTEGER NULL,
	no_installments INTEGER NULL,
	payment_date TIMESTAMP NULL,
	due NUMERIC(10,2) NULL,
	status INTEGER NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO sales (ord_no, customer_id, price_sum, Initial, delivery_date, remaining, interest, installments_month, no_installments, payment_date, due, status, create_by, created_at)
-- VALUES (10001, 4, 1000, 200, '2025-03-14 15:06:38.783', 800, 5, 12, 10, '2025-03-14 15:06:38.783', 141.66, 0, 1, '2025-03-14 15:06:38.783'),
-- 	   (10002, 2, 2000, 400, '2025-03-14 15:06:38.783', 1600, 5, 12, 10, '2025-03-14 15:06:38.783', 282, 0, 1, '2025-03-14 15:06:38.783');

CREATE TABLE IF NOT EXISTS purchased_products (
	purchase_id SERIAL PRIMARY KEY,
	sales_id INTEGER NULL,
	customer_id INTEGER NULL,
	product_id INTEGER NULL,
	quantity INTEGER NULL,
	price NUMERIC(10,2) NULL,
	discount NUMERIC(10,2) NULL,
	total NUMERIC(10,2) NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO purchased_products (sales_id, customer_id, product_id, quantity, price, discount, total, create_by, created_at)
-- VALUES (1, 4, 6, 1, 3000, 0, 3000, 1, '2025-03-14 15:06:38.783'),
-- 	   (1, 4, 4, 1, 1000, 0, 1000, 1, '2025-03-14 15:06:38.783');

CREATE TABLE IF NOT EXISTS payments (
	payment_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	ord_no INTEGER NULL,
	payment_no INTEGER NULL,
	sales_id INTEGER NULL,
	customer_id INTEGER NULL,
	person_who_paid VARCHAR(255) NULL,
	payer_document_type INTEGER NULL,
	payer_document_no VARCHAR(20) NULL,
	payer_phone VARCHAR(20) NULL,
	customer_email VARCHAR(255) NULL,
	currency VARCHAR(10) NULL,
	payment_method INTEGER NULL,
	amount NUMERIC(10,2) NULL,
	interest NUMERIC(10,2) NULL,
	installments_month INTEGER NULL,
	no_installments INTEGER NULL,	
	payment_date TIMESTAMP NULL,
	due NUMERIC(10,2) NULL,
	status INTEGER NULL,
	created_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS interest_earnings (
	earnings_id SERIAL PRIMARY KEY,
	sales_id INTEGER NULL,
	payment_id INTEGER NULL,
	customer_id INTEGER NULL,
	payment_no INTEGER NULL,
	ord_no INTEGER NULL,
	currency VARCHAR(10) NULL,
	interest NUMERIC(10,2) NULL,
	installments_month INTEGER NULL,
	no_installments INTEGER NULL,
	payment_date TIMESTAMP NULL,
	initial_debt NUMERIC(10,2) NULL,
	created_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
	role_id SERIAL PRIMARY KEY,
	role_name VARCHAR(50) NULL
);

-- INSERT INTO roles (role_id, role_name) VALUES
-- (1, 'Creator'),
-- (2, 'Owner'),
-- (3, 'Super Admin'),
-- (4, 'Administrator'),
-- (5, 'Manager'),
-- (6, 'Supervisor'),
-- (7, 'Operator'),
-- (8, 'Viewer');

CREATE TABLE IF NOT EXISTS permissions (
	permission_id SERIAL PRIMARY KEY,
	permission_name VARCHAR(50) NULL,
	description VARCHAR(255) NULL
);

-- INSERT INTO permissions (permission_id, permission_name, description) VALUES
-- (1, 'root_access', 'Can manage all aspects of the system'),
-- (2, 'system_admin', 'Can manage internal admin settings'),
-- (3, 'platform_admin', 'Can export reports'),
-- (4, 'ops_controller', 'Can delete records'),
-- (5, 'data_controller', 'Can manage user accounts'),
-- (6, 'data_handler', 'Can edit product info'),
-- (7, 'process_handler', 'Can create records'),
-- (8, 'sales_handler', 'Can manage sales and clients services'),
-- (9, 'read_advanced', 'Can view the dashboard'),
-- (10, 'read_only', 'Can create production records');

CREATE TABLE IF NOT EXISTS role_permissions (
	role_permission_id SERIAL PRIMARY KEY,
	role_id INTEGER NULL,
	permission_id INTEGER NULL
);

-- -- Creator: acceso total
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (1, 1),  -- root_access
-- (1, 2),  -- system_admin
-- (1, 3),  -- platform_admin
-- (1, 4),  -- ops_controller
-- (1, 5),  -- data_controller
-- (1, 6),  -- data_handler
-- (1, 7),  -- process_handler
-- (1, 8),  -- sales_handler
-- (1, 9),  -- read_advanced
-- (1, 10); -- read_only

-- -- Owner: acceso casi completo (excepto root_access)
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (2, 2),  -- system_admin
-- (2, 3),  -- platform_admin
-- (2, 4),  -- ops_controller
-- (2, 5),  -- data_controller
-- (2, 6),  -- data_handler
-- (2, 7),  -- process_handler
-- (2, 8),  -- sales_handler
-- (2, 9),  -- read_advanced
-- (2, 10); -- read_only

-- -- Super Admin
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (3, 3),  -- platform_admin
-- (3, 4),  -- ops_controller
-- (3, 5),  -- data_controller
-- (3, 6),  -- data_handler
-- (3, 7),  -- process_handler
-- (3, 8),  -- sales_handler
-- (3, 9),  -- read_advanced
-- (3, 10); -- read_only

-- -- Administrator
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (4, 4),  -- ops_controller
-- (4, 5),  -- data_controller
-- (4, 6),  -- data_handler
-- (4, 7),  -- process_handler
-- (4, 8),  -- sales_handler
-- (4, 9),  -- read_advanced
-- (4, 10); -- read_only

-- -- Manager
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (5, 6),  -- data_handler
-- (5, 7),  -- process_handler
-- (5, 8),  -- sales_handler
-- (5, 9),  -- read_advanced
-- (5, 10); -- read_only

-- -- Supervisor
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (6, 6),  -- data_handler
-- (6, 7),  -- process_handler
-- (6, 8),  -- sales_handler
-- (6, 9),  -- read_advanced
-- (6, 10); -- read_only

-- -- Operator
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (7, 8),  -- sales_handler
-- (7, 9),  -- read_advanced
-- (7, 10); -- read_only

-- -- Viewer
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (8, 9);  -- read_advanced

CREATE TABLE IF NOT EXISTS notifications (
	notification_id SERIAL PRIMARY KEY,
	from_user_id INTEGER NULL,
	to_user_id INTEGER NULL, -- Usuario al que va dirigida la notificación
	notification_type VARCHAR(50) NULL, -- Ej: 'message', 'alert', 'reminder'
	notification_content TEXT NULL,
	notification_link VARCHAR(255) NULL, -- Enlace relacionado con la notificación
	is_read INTEGER DEFAULT 0, -- 0: No leído, 1: Leído
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS push_subscriptions (
	subscription_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL REFERENCES users(user_id) ON DELETE CASCADE,
	endpoint TEXT NOT NULL,
	p256dh TEXT NOT NULL,
	auth TEXT NOT NULL,
	device_type VARCHAR(100) NULL,      -- mobile | desktop
	device_name VARCHAR(255) NULL,     -- iPhone, Chrome, Edge, etc
	user_agent TEXT NULL,
	is_active INTEGER NULL,
	created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS shippings (
	shippings_id SERIAL PRIMARY KEY,
	shipping_no VARCHAR(30) NULL,
	company_id INTEGER NULL,
	shipping_img VARCHAR(255) NULL,
	shipping_method INTEGER NULL,
	destination VARCHAR(255) NULL,
	delivery_date TIMESTAMP NULL,
	description TEXT NULL,
	status INTEGER NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS loads (
	load_id SERIAL PRIMARY KEY,
	shippings_id INTEGER NULL,
	customer_id INTEGER NULL,
	company_id INTEGER NULL,
	load_no VARCHAR(30) NULL,
	from_currency VARCHAR(10) NULL,
	price_per_kg NUMERIC(10,2) NULL,
	total_kg NUMERIC(10,3) NULL,
	price_sum NUMERIC(10,2) NULL,
	taxes NUMERIC(10,2) NULL,
	discount NUMERIC(10,2) NULL,
	price_total NUMERIC(10,2) NULL,
	to_currency VARCHAR(10) NULL,
	price_total_exchanged NUMERIC(10,2) NULL,
	destination VARCHAR(255) NULL,
	comment TEXT NULL,
	status INTEGER NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS loaded_products (
	loaded_products_id SERIAL PRIMARY KEY,
	load_id INTEGER NULL,
	product_id INTEGER NULL,
	quantity INTEGER NULL,
	total_kg NUMERIC(10,3) NULL,
	from_currency VARCHAR(10) NULL,
	total_kg_price NUMERIC(10,2) NULL,
	to_currency VARCHAR(10) NULL,
	total_price_exchanged NUMERIC(10,2) NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shipping_tracking (
    tracking_id SERIAL PRIMARY KEY,
    shipping_id INTEGER NULL,
    checkpoint_name VARCHAR(255),
    status VARCHAR(100),
    scanned_by INTEGER NULL,
    latitude DECIMAL(10,6),
    longitude DECIMAL(10,6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS settings (
	settings_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	company_currency VARCHAR(50) NULL,
	chipping_kg_price NUMERIC(10,2) NULL,
	created_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS access_rights (
	access_right_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	role_id INTEGER NULL,
	access_name VARCHAR(50) NULL,
	can_access INTEGER DEFAULT 0,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS service_rights (
	right_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	service_name VARCHAR(50) NULL,
	can_access INTEGER DEFAULT 0,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS extra_services (
	service_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	service_name VARCHAR(50) NULL,
	service_price INTEGER NULL,
	status INTEGER NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);