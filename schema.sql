CREATE DATABASE doingdone;

USE doingdone;

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  regDate DATETIME,
  email CHAR(100),
  name CHAR(100),
  password CHAR(60),
  contacts TEXT
);

CREATE TABLE project (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name CHAR(100),
  id_user INT
);

CREATE TABLE task (
  id INT AUTO_INCREMENT PRIMARY KEY,
  createDate DATETIME,
  doneDate DATETIME,
  name TEXT,
  file TEXT,
  limitDate DATETIME,
  id_project INT,
  id_user INT
);


CREATE UNIQUE INDEX iu_u_email ON user(email);
CREATE UNIQUE INDEX iu_p_name ON project(name);
CREATE INDEX i_t_name ON task(name(10));