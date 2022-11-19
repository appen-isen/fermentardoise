/************************************************************************
Creation Date:  2022-11-19
Description:    Creates the database tables and relations.
Usage:          psql -U postgres -d fermentardoise -a -f model.sql
                https://stackoverflow.com/a/23992045/12619942
************************************************************************/

DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS clients CASCADE;
DROP TABLE IF EXISTS payments CASCADE;

-- Table users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    login VARCHAR(255) NOT NULL,
    password_hash VARCHAR(50) NOT NULL
);

-- Table clients
CREATE TABLE clients (
    id SERIAL PRIMARY KEY,
    lastname VARCHAR(64) NOT NULL,
    firstname VARCHAR(64) NOT NULL
);

-- Table payments
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    amount FLOAT NOT NULL,
    comment TEXT,
    date_time TIMESTAMP NOT NULL,
    client_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,

    FOREIGN KEY (client_id) REFERENCES clients(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);