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
	members INTEGER NULL,
	status INTEGER NULL,
	status_by_admin INTEGER NULL
);

-- INSERT INTO users (parent_user, name, surname, email, phone, username, password, image, verified, birthday, signup_date, company_id, members, status)
-- VALUES (null, 'Lorenz', 'Knight', 'lorenz.knight@gmail.com', 763199480, 'lorenz_knight', 123456, 'profile_user_1_1742243935.jpg', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, 5, 1),
-- 	(1, 'Joel', 'Knight', 'joel.knight@gmail.com', null, 'joel_knight', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Shael', 'Knight', 'shael.knight@gmail.com', null, 'shael_knight', 123456, 'perfil.png', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'John', 'Doe', 'john.doe@gmail.com', null, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1),
-- 	(1, 'Lorenzo', 'Knight', 'lorenzo.knight@gmail.com', 763199480, 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1, null, 1);

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
	prise INTEGER NULL,
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
	customer_id INTEGER NULL,
	product_id INTEGER NULL,
	price NUMERIC(10,2) NULL,
	Initial NUMERIC(10,2) NULL,
	delivery_date TIMESTAMP NULL,
	remaining INTEGER NULL,
	interest INTEGER NULL,
	installments_month INTEGER NULL,
	no_installments INTEGER NULL,
	payment_date TIMESTAMP NULL,
	due NUMERIC(10,2) NULL,
	create_by INTEGER NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sales (customer_id, product_id, price, Initial, delivery_date, remaining, interest, installments_month, no_installments, payment_date, due, create_by, created_at)
VALUES (4, 4, 1000, 200, '2025-03-14 15:06:38.783', 800, 5, 12, 10, '2025-03-14 15:06:38.783', 141.66, 1, '2025-03-14 15:06:38.783');