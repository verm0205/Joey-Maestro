DROP TABLE IF EXISTS users;

CREATE TABLE users (
   id       INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
   name     TEXT    NOT NULL,
   username TEXT    NOT NULL UNIQUE,
   password TEXT    NOT NULL,
   role     TEXT    NOT NULL DEFAULT 'user' CHECK(role IN ('user','admin'))
);

INSERT INTO users (name, username, password, role) VALUES (
  'Joey Vermeulen',
  'joeyadmin',
  '$2y$12$KfSl6PSnsJjHmiKQf7dWD.GW2MMs3vU279/vkWi6h1WTAg8sI/xNq',
  'admin'
);