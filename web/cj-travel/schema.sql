USE cj_travel;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    name TEXT NOT NULL,
    INDEX users_index (email)
);

DROP TABLE IF EXISTS hotels;
CREATE TABLE hotels (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    price BIGINT NOT NULL CHECK (price >= 0),
    description text NOT NULL
);

DROP TABLE IF EXISTS hotel_bookings;
CREATE TABLE hotel_bookings (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    hotel_id BIGINT NOT NULL REFERENCES hotels(id),
    user_id BIGINT NOT NULL REFERENCES users(id),
    email_fc6ea87d8348147f2070ff8529482136 TEXT NOT NULL,
    phone TEXT NOT NULL,
    guest TEXT NOT NULL
);

INSERT INTO hotels (name, price, description) VALUES (
    'Hotel Melati',
    100000,
    'Affordable hotel'
);
INSERT INTO hotels (name, price, description) VALUES (
    'Hotel Mawar',
    750000,
    '3-star hotel'
);
INSERT INTO hotels (name, price, description) VALUES (
    'Hotel Anggrek',
    5000000,
    'Luxurious hotel'
);
