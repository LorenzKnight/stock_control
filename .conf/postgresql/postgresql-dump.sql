CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
	user_admin INTEGER NULL,
    name varchar(255) NULL,
	surname varchar(255) NULL,
	email varchar(255) NULL,
	phone varchar(20) NULL,
	username varchar(255) NULL,
	password varchar(255) NULL,
	image varchar(255) NULL,
	verified INTEGER NULL,
	birthday TIMESTAMP NULL,
	signup_date TIMESTAMP NULL,
	rank INTEGER NULL,
	members INTEGER NULL,
	status INTEGER NULL,
	status_by_admin INTEGER NULL
);

INSERT INTO users (name, surname, email, username, password, image, verified, birthday, signup_date, status)
VALUES ('Lorenz', 'Knight', 'lorenz.knight@gmail.com', 'lorenz_knight', 123456, 'profile_pic.jpg', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
       ('Joel', 'Knight', 'joel.knight@gmail.com', 'joel_knight', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
       ('Shael', 'Knight', 'shael.knight@gmail.com', 'shael_knight', 123456, 'shael_pic.png', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
	   ('John', 'Doe', 'john.doe@gmail.com', 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1);
