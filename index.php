<?php
session_start();

require_once('functions.php');
require_once('userdata.php');

// print_r('<pre>');
// var_dump($errors);
// print_r('</pre>');

// данные

date_default_timezone_set('Europe/Moscow');
$current_ts = strtotime('now midnight');
$title = 'Дела в порядке';
// $fio = 'Константин';

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


// если АВТОРИЗОВАННЫЙ

if (isset($_SESSION["user"])) {

	$fio = $_SESSION["user"]['name'];
	
	// отправка формы

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// добавление задачи
		// if (isset($_POST['taskSubmit'])) {

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

		// }  
	}


// метод GET

$projectKey = $_GET['project'] ?? false;

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

if (isset($_GET['add'])) {
	$add = true;
	$modal = includeTemplate('templates/modal.php', 
	[
		'projects' => $projects,
	]);
}

if (isset($_GET['show_completed'])) {
	$show_completed = $_GET['show_completed'];
	setcookie('showCompleted', $show_completed, strtotime("+30 days"));
	header('Location: /index.php');
}


// куки

if (isset($_COOKIE['showCompleted'])) {
	if ($_COOKIE['showCompleted']) {
		$checked = 'checked';
		$show_completed = 0;
	} else {
		$checked = '';
		$show_completed = 1;
		foreach ($tasksSelect as $k => $val) {
			if ($val['done'] == 'Да') {
				unset($tasksSelect[$k]);
			}
		}
	}
} else {
		$checked = '';
		$show_completed = 1;
		foreach ($tasksSelect as $k => $val) {
			if ($val['done'] == 'Да') {
				unset($tasksSelect[$k]);
			}
		}
}


$content = includeTemplate('templates/index.php', 
[
	'tasks' => $tasksSelect,
	'show_completed' => $show_completed,
	'checked' => $checked,
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


// если ГОСТЬ

} else {


// отправка формы

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		// if (isset($_POST['loginSubmit'])) {}

		$required = ['email', 'password']; 
		$errors = [];
		$values = [];

	  // $nameNew = $_POST['name'] ?? '';
	  // $projectNew = $_POST['project'] ?? '';
	  // $dateNew = $_POST['date'] ?? '';

		foreach ($_POST as $k => $val) {
			$values = $_POST;
			if (in_array($k, $required) && $val == '') { 
				$errors[$k] = 'это поле требуется заполнить';
			}
		}

		if (count($errors)) {
			$login = true;
		} else {
			$user = searchUserByEmail($values['email'], $users);
			if ($user) {
			  if (password_verify($values['password'], $user['password'])) {
			    $_SESSION['user'] = $user;
			    header("Location: /index.php");
			  } else {
			  	$login = true;
			  	$errors['password'] = 'Вы ввели неверный пароль';
			  }
			} else {
				$login = true;
				$errors['email'] = 'Такой логин не найден';
			}
		}

	}

	if (isset($_GET['login'])) {
		$login = true;
	}

	$guest = includeTemplate('templates/guest.php', 
	[
		'login' => $login,
		'errors' => $errors,
		'values' => $values,
	]);
	print($guest);

}

