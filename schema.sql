CREATE DATABASE doingdone;

USE doingdone;

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reg_date DATETIME,
  email CHAR(100),
  name CHAR(100),
  password CHAR(60),
  contacts TEXT
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project CHAR(100),
  id_user INT
);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  create_date DATETIME,
  done_date DATETIME,
  task TEXT,
  file TEXT,
  dateDeadline DATETIME,
  done TINYINT(1),
  id_project INT,
  id_user INT
);


CREATE UNIQUE INDEX iu_u_email ON user(email);
CREATE INDEX i_t_name ON tasks(task(10));