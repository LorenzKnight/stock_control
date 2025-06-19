CREATE TABLE IF NOT EXISTS users (
	user_id SERIAL PRIMARY KEY,
	parent_user INTEGER NULL,
	name VARCHAR(255) NULL,
	surname VARCHAR(255) NULL,
	email VARCHAR(255) NULL,
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
-- VALUES (null, 'Lorenz', 'Knight', 'lorenz.knight@gmail.com', 763199480, 'lorenz_knight', 123456, 'profile_user_1_1742243935.jpg', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, 5, 1),
-- 	(1, 'Joel', 'Knight', 'joel.knight@gmail.com', null, 'joel_knight', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Shael', 'Knight', 'shael.knight@gmail.com', null, 'shael_knight', 123456, 'perfil.png', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'John', 'Doe', 'john.doe@gmail.com', null, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Lorenzo', 'Knight', 'lorenzo.knight@gmail.com', 763199480, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1);

CREATE TABLE IF NOT EXISTS packages (
	package_id SERIAL PRIMARY KEY,
	package_name VARCHAR(255) NULL,
	package_image VARCHAR(255) NULL,
	package_description TEXT NULL,
	package_price INTEGER NULL,
	members_limit INTEGER NULL, -- Límite de miembros
	admin_limit INTEGER NULL, -- Límite de administradores
	branch_affiliate_limit INTEGER NULL, -- Límite de sucursales afiliadas
	products_limit INTEGER NULL, -- Límite de productos
	package_duration INTEGER NULL, -- Duración en dias
	package_status INTEGER NULL, -- 0: Inactivo, 1: Activo
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO packages (
--   package_name, package_description, package_price, members_limit, admin_limit, branch_affiliate_limit, products_limit, package_duration, package_status
-- ) VALUES 
-- ('Tried Pack',  
--  'Perfect to explore the system with no commitment. Includes 1 user, limited access to essential features, and 1 affiliate branch. Great for solo entrepreneurs.', 
--  NULL, 1, 0, 1, 5, 30, 1),

-- ('Basic Pack',  
--  'Basic plan for individual users. Includes 1 member, essential tools, 1 affiliate branch, and up to 10 products. Ideal for freelancers or micro-businesses.', 
--  2, 1, 1, 1, 10, NULL, 1),

-- ('Mini Pack',  
--  'Designed for small teams. Supports up to 5 members, shared admin access, 1 affiliate branch, and up to 50 products. Perfect for local shops.', 
--  10, 5, 1, 1, 50, NULL, 1),

-- ('Smart Pack', 
--  'A great choice for growing teams. Allows up to 10 members, 1 admin, 2 affiliate branches, and up to 100 products. Ideal for emerging small businesses.', 
--  20, 10, 1, 2, 100, NULL, 1),

-- ('Plus Pack',  
--  'Mid-range capacity for moderately expanding businesses. Up to 15 members, 2 admins, 3 affiliate branches, and 150 products.', 
--  30, 15, 2, 3, 150, NULL, 1),

-- ('Growth Pack', 
--  'Optimized for expansion. Includes 20 members, 2 admins, 4 affiliate branches, and up to 200 products. Balanced for scale and control.', 
--  40, 20, 2, 4, 200, NULL, 1),

-- ('Boost Pack',  
--  'A boost for larger operations. Supports 25 members, 3 admins, 5 affiliate branches, and 250 products. Ideal for chains and franchises.', 
--  50, 25, 3, 5, 250, NULL, 1),

-- ('Power Pack',  
--  'Strong operational power. Allows 30 users, 3 admins, 6 affiliate branches, and 300 products. Great for businesses with multiple locations.', 
--  60, 30, 3, 6, 300, NULL, 1),

-- ('Max Pack',    
--  'For large business structures. Includes 35 members, 4 admins, 7 affiliate branches, and up to 350 products. Perfect for diversified operations.', 
--  70, 35, 4, 7, 350, NULL, 1),

-- ('Super Pack',  
--  'Wide coverage for big enterprises. Supports 40 members, 4 admins, 8 affiliate branches, and 400 products. Recommended for regional operations.', 
--  80, 40, 4, 8, 400, NULL, 1),

-- ('Mega Pack',   
--  'Robust solution for high-activity organizations. 45 members, 5 admins, 9 affiliate branches, and 450 products.', 
--  90, 45, 5, 9, 450, NULL, 1),

-- ('Ultra Pack',  
--  'The full experience. Includes 50 members, 5 admins, 10 affiliate branches, and up to 500 products. Recommended for well-structured companies.', 
--  100, 50, 5, 10, 500, NULL, 1);

CREATE TABLE IF NOT EXISTS subscriptions (
	subsc_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	members_packs INTEGER NULL,
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
	parent_company INTEGER NULL,
	company_name VARCHAR(255) NULL,
	organization_no INTEGER NULL,
	company_address VARCHAR(255) NULL,
	company_phone VARCHAR(20) NULL,
	company_logo VARCHAR(255) NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT INTO companies (user_id, company_type, company_name, organization_no, company_address, company_phone, company_logo, created_at)
-- VALUES (1, null, 'My Company', 1234123, 'Siriusgatan 102', 763199480, 'logo_user_1_1742063586.png', '2025-03-14 15:06:38.783');

CREATE TABLE IF NOT EXISTS products (
	product_id SERIAL PRIMARY KEY,
	company_id INTEGER NULL,
	create_by INT NOT NULL,
	product_image VARCHAR(255) NULL,
	product_name VARCHAR(255) NULL,
	product_type INTEGER NULL,
	product_mark INTEGER NULL,
	product_model INTEGER NULL,
	product_sub_model INTEGER NULL,
	product_year INTEGER NULL,
	description TEXT NULL,
	currency VARCHAR(10) NULL,
	prise INTEGER NULL,
	quantity INTEGER NULL,
	status INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS category (
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
	customer_phone VARCHAR(20) NULL,
	customer_address VARCHAR(255) NULL,
	customer_birthday TIMESTAMP NULL,
	customer_document_type INTEGER NULL,
	customer_document_no VARCHAR(20) NULL,
	marital_status INTEGER NULL,
	references_1 VARCHAR(255) NULL,
	references_1_phone VARCHAR(20) NULL,
	references_2 VARCHAR(255) NULL,
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
-- (1, 'manage_all', 'Can manage all aspects of the system'),
-- (2, 'manage_intern_admin', 'Can manage internal admin settings'),
-- (3, 'export_reports', 'Can export reports'),
-- (4, 'delete_data', 'Can delete records'),
-- (5, 'manage_users', 'Can manage user accounts'),
-- (6, 'edit_data', 'Can edit product info'),
-- (7, 'create_data', 'Can create records'),
-- (8, 'manage_sales', 'Can manage sales and clients services'),
-- (9, 'view_dashboard', 'Can view the dashboard'),
-- (10, 'create_prod_data', 'Can create production records');

CREATE TABLE IF NOT EXISTS role_permissions (
	role_permission_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	role_id INTEGER NULL,
	permission_id INTEGER NULL
);

-- -- Creator: acceso total
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (1, 1),  -- manage_all
-- (1, 2),  -- manage_intern_admin
-- (1, 3),  -- export_reports
-- (1, 4),  -- delete_data
-- (1, 5),  -- manage_users
-- (1, 6),  -- edit_data
-- (1, 7),  -- create_data
-- (1, 8),  -- manage_sales
-- (1, 9),  -- view_dashboard
-- (1, 10); -- create_prod_data

-- -- Owner: acceso casi completo (excepto manage_all)
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (2, 2),  -- manage_intern_admin
-- (2, 3),  -- export_reports
-- (2, 4),  -- delete_data
-- (2, 5),  -- manage_users
-- (2, 6),  -- edit_data
-- (2, 7),  -- create_data
-- (2, 8),  -- manage_sales
-- (2, 9),  -- view_dashboard
-- (2, 10); -- create_prod_data

-- -- Super Admin
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (3, 3),  -- export_reports
-- (3, 4),  -- delete_data
-- (3, 5),  -- manage_users
-- (3, 6),  -- edit_data
-- (3, 7),  -- create_data
-- (3, 8),  -- manage_sales
-- (3, 9),  -- view_dashboard
-- (3, 10); -- create_prod_data

-- -- Administrator
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (4, 4),  -- delete_data
-- (4, 5),  -- manage_users
-- (4, 6),  -- edit_data
-- (4, 7),  -- create_data
-- (4, 8),  -- manage_sales
-- (4, 9),  -- view_dashboard
-- (4, 10); -- create_prod_data

-- -- Manager
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (5, 6),  -- edit_data
-- (5, 7),  -- create_data
-- (5, 8),  -- manage_sales
-- (5, 9),  -- view_dashboard
-- (5, 10); -- create_prod_data

-- -- Supervisor
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (6, 6),  -- edit_data
-- (6, 7),  -- create_data
-- (6, 8),  -- manage_sales
-- (6, 9),  -- view_dashboard
-- (6, 10); -- create_prod_data

-- -- Operator
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (7, 8),  -- manage_sales
-- (7, 9),  -- view_dashboard
-- (7, 10); -- create_prod_data

-- -- Viewer
-- INSERT INTO role_permissions (role_id, permission_id) VALUES
-- (8, 9);  -- view_dashboard