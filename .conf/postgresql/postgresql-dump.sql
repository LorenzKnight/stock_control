CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    name varchar(255) NULL,
	surname varchar(255) NULL,
	email varchar(255) NULL,
	username varchar(255) NULL,
	password varchar(255) NULL,
	image varchar(255) NULL,
	verified INTEGER NULL,
	birthday TIMESTAMP NULL,
	signup_date TIMESTAMP NULL,
	rank INTEGER NULL,
	status INTEGER NULL,
	status_by_admin INTEGER NULL
);

INSERT INTO users (name, surname, email, username, password, image, verified, birthday, signup_date, status)
VALUES ('Lorenz', 'Knight', 'lorenz.knight@gmail.com', 'lorenz_knight', 123456, 'profile_pic.jpg', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
       ('Joel', 'Knight', 'joel.knight@gmail.com', 'joel_knight', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
       ('Shael', 'Knight', 'shael.knight@gmail.com', 'shael_knight', 123456, 'shael_pic.png', 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1),
	   ('John', 'Doe', 'john.doe@gmail.com', 'john_doe', 123456, null, 0, '1984-09-03 00:00:00', '2022-10-18 00:00:00', 1);

-- select * from users;

CREATE TABLE IF NOT EXISTS listings (
	lid SERIAL PRIMARY KEY,
    user_id INTEGER NULL,
    list_name varchar(255) NULL,
    public INTEGER NULL,
	list_date TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS song (
	sid SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	list_id INTEGER NULL,
	artist varchar(255) NULL,
	song_name varchar(255) NULL,
	report INTEGER NULL,
	public INTEGER NULL,
	song_date TIMESTAMP NULL
);

-- ALTER TABLE users  
-- ADD COLUMN image varchar(255) NULL;

-- update users set name = 'Lorenz', image = 'profile_pic.jpg' where user_id = 1

-- INSERT INTO river (user_id, content, status, post_date)
-- VALUES (1, 'post 1', 1, '2022-10-18 00:00:00'),
--        (2, 'post 2', 1, '2022-10-18 00:00:00'),
--        (3, 'post 3', 1, '2022-10-18 00:00:00');
       
-- select * from river;

-- CREATE TABLE IF NOT EXISTS report (
-- );

-- CREATE TABLE IF NOT EXISTS likes (
-- 	like_id SERIAL PRIMARY KEY,
-- 	stars INTEGER null,
-- 	rate_bonus FLOAT NULL,
-- 	user_id INTEGER NULL,
-- 	to_user_id INTEGER NULL,
-- 	object_id INTEGER NULL,
-- 	comment_id INTEGER null,
-- 	rate_date TIMESTAMP NULL
-- );

-- INSERT INTO rates (stars, rate_bonus, user_id, post_id, rate_date)
-- VALUES (4, 0.2, 2, 3, '2022-10-18 00:00:00'),
--        (3, 0.5, 3, 3, '2022-10-18 00:00:00');

-- select * from rates;

CREATE TABLE IF NOT EXISTS comments (
	comment_id SERIAL PRIMARY KEY,
	user_id INTEGER NULL,
	song_id INTEGER NULL,
	comment varchar(255) NULL,
	comment_date TIMESTAMP NULL
);

-- CREATE TABLE IF NOT EXISTS followers (
-- 	follow_id SERIAL PRIMARY KEY,
-- 	user_id INTEGER null,
-- 	is_following INTEGER null,
-- 	accepted INTEGER null,
-- 	condition varchar(255) null, --if it is limited or not
-- 	follow_date TIMESTAMP null
-- );

-- CREATE TABLE IF NOT EXISTS log (
-- 	log_id SERIAL PRIMARY KEY,
-- 	from_userid INTEGER null,
-- 	action varchar(255) null, -- if it's comment, if it's follow, if it's rate...
-- 	obj_id INTEGER null, -- comment id, follow id, rate lever exemp: 3 star....
-- 	to_userid INTEGER null,
-- 	commentary varchar(255) null,
-- 	checked INTEGER DEFAULT 0 NOT null,
-- 	log_date TIMESTAMP null
-- );