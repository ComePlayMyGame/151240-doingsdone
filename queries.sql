INSERT INTO user SET 
email = 'ignat.v@gmail.com', 
name = 'Игнат',
password = '$2y$10$OqvsKHQwr0Wk6FMZDoHo1uHoXd4UdxJG/5UDtUiie00XaxMHrW8ka';

INSERT INTO user SET 
email = 'kitty_93@li.ru', 
name = 'Леночка',
password = '$2y$10$bWtSjUhwgggtxrnJ7rxmIe63ABubHQs0AS0hgnOo41IEdMHkYoSVa';

INSERT INTO project SET 
name = 'Входящие', 
id_user = 1;

INSERT INTO project SET 
name = 'Учеба', 
id_user = 2;

INSERT INTO task SET 
name = 'Собеседование в IT компании',
limitDate = '01.06.2018',
id_project = 1,
id_user = 1;

INSERT INTO task SET 
name = 'Выполнить тестовое задание',
limitDate = '25.05.2018',
id_project = 1,
id_user = 1;


-- получить список из всех проектов для одного пользователя
SELECT project.name FROM project 
JOIN user 
ON id_user = user.id 
WHERE user.name = 'Леночка';

-- получить список из всех задач для одного проект
SELECT task.name, createDate, doneDate, limitDate FROM task 
JOIN project 
ON id_project = project.id 
WHERE project.name = 'учеба';

-- пометить задачу как выполненную
-- (update) использовать пример из удаления записей через флаг, нужно создать еще одну колонку в таблице будет

-- получить все задачи для завтрашнего дня
-- select

-- обновить название задачи по её идентификатору
-- update

