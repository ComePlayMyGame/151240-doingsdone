<?php

require_once('functions.php');

// print_r('<pre>');
// var_dump($errors);
// print_r('</pre>');

// данные

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


// метод GET

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

if (isset($_GET[add])) {
	$add = true;
	$modal = includeTemplate('templates/modal.php', 
	[
		'projects' => $projects,
	]);
}


// метод POST

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$required = ['name', 'project', 'date']; 
	$rules = ['date']; 
	$errors = [];
	$values = [];

  $nameNew = $_POST['name'] ?? '';
  $projectNew = $_POST['project'] ?? '';
  $dateNew = $_POST['date'] ?? '';

	foreach ($_POST as $k => $val) {
		$values = $_POST;
		if (in_array($k, $required) && $val == '') { 
			$errors[$k] = 'это поле требуется заполнить';
		}
		if (in_array($k, $rules) && !($val == '')) { 
			if (!strtotime($val)) {
		    $errors[$k] = 'неверный формат';
		  }
		}
	}

	if (count($errors)) {
		$add = true;
		$modal = includeTemplate('templates/modal.php', 
		[
			'projects' => $projects,
			'errors' => $errors,
			'values' => $values,
		]);
	} else {
		if (isset($_FILES['preview']['name'])) {
			$fileName = $_FILES['preview']['name'];
			$fileTempPath = $_FILES['preview']['tmp_name'];
			$filePath = __DIR__.'/'.$fileName;
			move_uploaded_file($fileTempPath, $filePath);
		}
		$taskNew = [
			'task' => $nameNew,
			'dateDeadline' => $dateNew,
			'project' => $projectNew,
			'done' => 'Нет'
		];
		$tasksSelect[] = $taskNew;
	}

}


// подключение шаблонов

$content = includeTemplate('templates/index.php', 
[
	'tasks' => $tasksSelect,
]);

$page = includeTemplate('templates/layout.php', 
[
  'content' => $content,
  'projects' => $projects,
  'tasks' => $tasks,
  'title' => $title,
  'fio' => $fio,
  'add' => $add,
  'modal' => $modal,
]);

print($page);



