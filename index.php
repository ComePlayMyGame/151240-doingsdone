<?php
session_start();

require_once('functions.php');
require_once('mysql_helper.php');
// require_once('userdata.php');
require_once('init.php');


// данные

date_default_timezone_set('Europe/Moscow');
$current_ts = strtotime('now midnight');
$title = 'Дела в порядке';
// $fio = 'Константин';


// $tasks = [
// 	[
// 		'task' => 'Собеседование в IT компании',
// 		'dateDeadline' => '01.06.2018',
// 		'project' => 'Работа',
// 		'done' => 'Нет'
// 	],
// 	[
// 		'task' => 'Выполнить тестовое задание',
// 		'dateDeadline' => '25.05.2018',
// 		'project' => 'Работа',
// 		'done' => 'Нет'
// 	],
// 	[
// 		'task' => 'Сделать задание первого раздела',
// 		'dateDeadline' => '21.04.2018',
// 		'project' => 'Учеба',
// 		'done' => 'Да'
// 	],
// 	[
// 		'task' => 'Встреча с другом',
// 		'dateDeadline' => '22.04.2018',
// 		'project' => 'Входящие',
// 		'done' => 'Нет'
// 	],
// 	[
// 		'task' => 'Купить корм для кота',
// 		'dateDeadline' => 'Нет',
// 		'project' => 'Домашние дела',
// 		'done' => 'Нет'
// 	],
// 	[
// 		'task' => 'Заказать пиццу',
// 		'dateDeadline' => 'Нет',
// 		'project' => 'Домашние дела',
// 		'done' => 'Нет'
// 	]
// ];

$sql = "
SELECT `email`, `name`, `password` FROM `user` 
";

$query = mysqli_query($connect, $sql);

if ($query) {
	$users = mysqli_fetch_all($query, MYSQLI_ASSOC);
} else {
	$error = mysqli_error($connect);
	$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	print($page);
	exit();
}


	$sql = "
	SELECT `tasks`.`id`, `tasks`.`task`, `dateDeadline`, `done`, `projects`.`project` FROM `tasks` 
	JOIN `projects` 
	ON `id_project` = `projects`.`id`
	";

	$query = mysqli_query($connect, $sql);
	
	if ($query) {

		$tasks = mysqli_fetch_all($query, MYSQLI_ASSOC);

		// foreach ($tasksArray as $k => $val) {
		// 	$tasks[] = $val['name'];
		// }

		// print_r("<pre>");
		// var_dump($tasksArray);
		// print_r("</pre>");

	} else {
		$error = mysqli_error($connect);
		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
		print($page);
		exit();
	}


// если АВТОРИЗОВАННЫЙ

if (isset($_SESSION["user"])) {

	$fio = $_SESSION["user"]['name'];

	$sql = "
	SELECT `projects`.`project` FROM `projects`
	JOIN `user`
	ON `id_user` = `user`.`id`
	WHERE `user`.`name` = '$fio'
	";

	$query = mysqli_query($connect, $sql);
	
	if ($query) {
		$projectsArray = mysqli_fetch_all($query, MYSQLI_ASSOC);

		foreach ($projectsArray as $k => $val) {
			$projects[] = $val['project'];
		}

		// print_r("<pre>");
		// var_dump($projects);
		// print_r("</pre>");

	} else {
		$error = mysqli_error($connect);
		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
		print($page);
		exit();
	}

// 	$projects = [
// 	'Все',
// 	'Входящие',
// 	'Учеба',
// 	'Работа',
// 	'Домашние дела',
// 	'Авто'
// ];

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
			} else {
				$fileTempPath = '';
			}
			// $taskNew = [
				// 'task' => $nameNew,
				// 'dateDeadline' => $dateNew,
				// 'project' => $projectNew,
				// 'done' => 'Нет',
			// ];
			// $tasksSelect[] = $taskNew;

			$sql = "
			SELECT `projects`.`id` FROM `projects`
			JOIN `user`
			ON `id_user` = `user`.`id`
			WHERE 
			`name` = '$fio' && `project` = '$projectNew'
			";

			$query = mysqli_query($connect, $sql);
			if ($query) {
				$projectID = mysqli_fetch_array($query, MYSQLI_NUM);
			} else {
				$error = mysqli_error($connect);
				$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
				print($page);
				exit();
			}

			$sql = "
			INSERT INTO `tasks` SET
			`create_date` = 'NOW()',
			`task` = '$nameNew',
			`dateDeadline` = '$dateNew',
			`id_project` = '$projectID[0]',
			`done` = '0',
			`file` = '$fileTempPath'
			";

			$query = mysqli_query($connect, $sql);
			if ($query) {
				header('location: /index.php');
			} else {
				$error = mysqli_error($connect);
				$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
				print($page);
				exit();
			}

		}

		// }  
	}


// метод GET

$userId = $_GET['done'] ?? false;


if ($userId) {

		foreach ($tasks as $val) {
			if ($val['id'] == $userId) {
				$yn_done = $val['done'];
			}
		}

	// foreach ($tasks as $val) {
		// if ($val['id'] == $userId) {

	// $sql = "
	// SELECT done FROM `tasks`
	// WHERE id = $userId
	// ";

	// $query = mysqli_query($connect, $sql);
	// if ($query) {
	// 	$done = mysqli_fetch_all($query, MYSQLI_ASSOC);
	// 	print_r($done);

	// 	// header('location: /index.php');
	// } else {
	// 	$error = mysqli_error($connect);
	// 	$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	// 	print($page);
	// 	exit();
	// }


	// if ($yn_done == 1) {

	//     $sql = "
	//     UPDATE tasks SET 
	//     done = 0 
	//     WHERE id = $userId
	//     ";

 //    	$query = mysqli_query($connect, $sql);
	//     	if ($query) {
	//     		header('Location: /index.php');
	//     	} else {
	//     		$error = mysqli_error($connect);
	//     		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	//     		print($page);
	//     		exit();
	//     	}

	//     }

	// 		else if ($yn_done == 0) {
	//         $sql = "
	//         UPDATE tasks SET 
	//         done = 1 
	//         WHERE id = $userId
	//         ";

	//         	$query = mysqli_query($connect, $sql);
	//         	if ($query) {
	//         		header('Location: /index.php');
	//         	} else {
	//         		$error = mysqli_error($connect);
	//         		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	//         		print($page);
	//         		exit();
	//         	}

	//         }
	


			$sql = "
			UPDATE  tasks
			SET     done = IF(done = 1, 0, 1)
			WHERE   id = $userId
			";

			$query = mysqli_query($connect, $sql);
			if ($query) {
				header('Location: /index.php');
			} else {
				$error = mysqli_error($connect);
				$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
				print($page);
				exit();
			}



			// $sql = "
			// INSERT INTO `tasks` SET
			// `create_date` = 'NOW()',
			// `task` = '$nameNew',
			// `dateDeadline` = '$dateNew',
			// `id_project` = '$projectID[0]',
			// `done` = '0',
			// `file` = '$fileTempPath'
			// ";

			// $query = mysqli_query($connect, $sql);
			// if ($query) {
			// 	header('location: /index.php');
			// } else {
			// 	$error = mysqli_error($connect);
			// 	$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
			// 	print($page);
			// 	exit();
			// }

		// }
	// }
}


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

		if (isset($_POST['loginSubmit'])) {

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

	if (isset($_POST['registerSubmit'])) {

			$required = ['email', 'password', 'name'];
			$errors = [];
			$values = [];

		  $nameUser = $_POST['name'] ?? '';
		  $emailUser = $_POST['email'] ?? '';
		  $passwordUser = $_POST['password'] ?? '';

			foreach ($_POST as $k => $val) {

				$values = $_POST;

				if (in_array($k, $required) && $val == '') {
					$errors[$k] = 'это поле требуется заполнить';
				} else {
						foreach ($users as $val) {
							if ($emailUser == $val['email']) {
								$errors['email'] = 'такой email уже существует';
							}
						}
				}

			}

			// var_dump("один ".$emailUser." два ".$users['email']);
			// var_dump($users['email']);
			foreach ($users as $k => $val) {
				
			}

			if (count($errors)) {
				$register = true;
				$page_reg = includeTemplate('templates/register.php', 
				[
					'register' => $register,
					'errors' => $errors,
					'values' => $values,
				]);
				print($page_reg);

			} else {

				$passwordHash = password_hash($passwordUser, PASSWORD_DEFAULT);

				$sql = "
				INSERT INTO `user`
				(`reg_date`, `email`, `name`, `password`)
				VALUES
				(NOW(), '$emailUser', '$nameUser', '$passwordHash')
				";

				$query = mysqli_query($connect, $sql);
				if ($query) {
					header('location: /index.php?login');
				} else {
					$error = mysqli_error($connect);
					$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
					print($page);
					exit();
				}

			}

	}


	}

	if (isset($_GET['login'])) {
		$login = true;
	} else if (isset($_GET['register'])) {
		print(includeTemplate('templates/register.php', []));
	}

	$guest = includeTemplate('templates/guest.php', 
	[
		'login' => $login,
		'errors' => $errors,
		'values' => $values,
	]);
	print($guest);

}

