CREATE TABLE users (
       id       INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
       name     VARCHAR(255) NOT NULL,
       username VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       role     VARCHAR(20)  NOT NULL DEFAULT 'user' CHECK(role IN ('user','admin'))
);

INSERT INTO users (name, username, password, role) VALUES (
      'Joey Vermeulen',
      'joeyadmin',
      '$2y$12$KfSl6PSnsJjHmiKQf7dWD.GW2MMs3vU279/vkWi6h1WTAg8sI/xNq',
      'admin'
    );