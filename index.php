<?php

require_once('functions.php');


// переменные

date_default_timezone_set('Europe/Moscow');
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


$projectKey = $_GET[project] ?? false;

if ($projectKey) {

	if (count($projects) > $projectKey) {
		foreach ($tasks as $k => $val) {
			if ($val['project'] == $projects[$projectKey]) {
				$tasksSelect[] = $val;
			}
		}
	} else {
		http_response_code(404);
		exit('<b>Ошибка 404</b>');
	}
	
} else {
	$tasksSelect = $tasks;
}



// подключение шаблонов

$content = includeTemplate('templates/index.php', 
[
	'tasks' => $tasksSelect

]);

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


