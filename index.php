<?php

require_once('functions.php');

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');
// текущая метка времени
$current_ts = strtotime('now midnight');

$show_complete_tasks = 1;

$title = 'Дела в порядке';

$fio = 'Константин';

$projects = [
	'Все',
	'Входящие',
	'Учеба',
	'Работа',
	'Домашние дела',
	'Авто'
];

$tasks = [
	[
		'task' => 'Собеседование в IT компании',
		'dateDeadline' => '01.06.2018',
		'project' => 'Работа',
		'done' => 'Нет'
	],
	[
		'task' => 'Выполнить тестовое задание',
		'dateDeadline' => '25.05.2018',
		'project' => 'Работа',
		'done' => 'Нет'
	],
	[
		'task' => 'Сделать задание первого раздела',
		'dateDeadline' => '21.04.2018',
		'project' => 'Учеба',
		'done' => 'Да'
	],
	[
		'task' => 'Встреча с другом',
		'dateDeadline' => '22.04.2018',
		'project' => 'Входящие',
		'done' => 'Нет'
	],
	[
		'task' => 'Купить корм для кота',
		'dateDeadline' => 'Нет',
		'project' => 'Домашние дела',
		'done' => 'Нет'
	],
	[
		'task' => 'Заказать пиццу',
		'dateDeadline' => 'Нет',
		'project' => 'Домашние дела',
		'done' => 'Нет'
	]
];


$content = includeTemplate('templates/index.php', ['tasks' => $tasks]);
$page = includeTemplate('templates/layout.php', 
[
  'content' => $content,
  'projects' => $projects,
  'tasks' => $tasks,
  'title' => $title,
  'fio' => $fio
]);

print($page);


?>


