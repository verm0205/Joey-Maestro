DROP TABLE IF EXISTS users;

CREATE TABLE users (
   id       INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
   name     VARCHAR     NOT NULL,
   username VARCHAR     NOT NULL UNIQUE,
   password VARCHAR     NOT NULL,
   role     VARCHAR     NOT NULL DEFAULT 'user' CHECK(role IN ('user','admin'))
);

INSERT INTO users (name, username, password, role) VALUES (
  'Joey Vermeulen',
  'joeyadmin',
  '$2y$12$KfSl6PSnsJjHmiKQf7dWD.GW2MMs3vU279/vkWi6h1WTAg8sI/xNq',
  'admin'
);