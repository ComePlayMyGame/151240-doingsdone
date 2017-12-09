INSERT INTO user 
(reg_date, email, name, password, contacts) 
VALUES
(NOW(),'ignat.v@gmail.com', 'Игнат', '$2y$10$OqvsKHQwr0Wk6FMZDoHo1uHoXd4UdxJG/5UDtUiie00XaxMHrW8ka', 'Контакты'), 
(NOW(), 'kitty_93@li.ru', 'Леночка', '$2y$10$bWtSjUhwgggtxrnJ7rxmIe63ABubHQs0AS0hgnOo41IEdMHkYoSVa', ''), 
(NOW(), 'warrior07@mail.ru', 'Руслан', '$2y$10$2OxpEH7narYpkOT1H5cApezuzh10tZEEQ2axgFOaKW.55LxIJBgWW', 'Мои контакты');


INSERT INTO projects
(project, id_user)
VALUES
('Все', ''),
('Входящие', 1),
('Учеба', 1),
('Работа', 1),
('Домашние дела', 1),
('Авто', 1),
('Входящие', 2),
('Учеба', 2),
('Работа', 2),
('Домашние дела', 2),
('Авто', 2),
('Входящие', 3),
('Учеба', 3),
('Работа', 3),
('Домашние дела', 3),
('Авто', 3);


INSERT INTO tasks
(create_date, task, dateDeadline, done, id_project, id_user)
VALUES
(NOW(), 'Собеседование в IT компании', '01.06.2018', 0, 4, 1),
(NOW(), 'Выполнить тестовое задание', '25.05.2018', 0, 4, 1),
(NOW(), 'Сделать задание первого раздела', '21.04.2018', 1, 3, 1),
(NOW(), 'Встреча с другом', '22.04.2018', 0, 2, 1),
(NOW(), 'Купить корм для кота', '20.01.2018', 0, 5, 1),
(NOW(), 'Заказать пиццу', '22.04.2017', 0, 5, 1);



-- получить список из всех проектов для одного пользователя
SELECT project.name FROM project 
JOIN user 
ON id_user = user.id 
WHERE user.name = 'Леночка';

-- получить список из всех задач для одного проект
SELECT task.name, create_date, done_date, limit_date FROM task 
JOIN project 
ON id_project = project.id 
WHERE project.name = 'учеба';

-- пометить задачу как выполненную
-- (update) использовать пример из удаления записей через флаг, нужно создать еще одну колонку в таблице будет

-- получить все задачи для завтрашнего дня
-- select

-- обновить название задачи по её идентификатору
-- update

