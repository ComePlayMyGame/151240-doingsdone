<?php
session_start();

require_once('functions.php');
require_once('mysql_helper.php');
require_once('init.php');


date_default_timezone_set('Europe/Moscow');
$current_ts = strtotime('now midnight');
$title = 'Дела в порядке';


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

// если АВТОРИЗОВАННЫЙ

if (isset($_SESSION["user"])) {

		$emailSID = $_SESSION['user']['email'];
		$fio = $_SESSION["user"]['name'];

		$fio = mysqli_real_escape_string($connect, $fio);

		$sql = "
		SELECT `tasks`.`id`, `file`, `tasks`.`task`, `dateDeadline`, `done`, `projects`.`project` FROM `tasks` 
		JOIN `projects` 
		ON `id_project` = `projects`.`id`
		JOIN `user` 
		ON `tasks`.`id_user` = `user`.`id`
		WHERE	`email` = '$emailSID'
		";

		$query = mysqli_query($connect, $sql);
		
		if ($query) {

			$tasks = mysqli_fetch_all($query, MYSQLI_ASSOC);

		} else {
			$error = mysqli_error($connect);
			$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
			print($page);
			exit();
		}


	$sql = "
	SELECT `projects`.`project` FROM `projects`
	JOIN `user`
	ON `id_user` = `user`.`id`
	WHERE	`email` = '$emailSID'
	";

	$query = mysqli_query($connect, $sql);
	
	if ($query) {
		$projectsArray = mysqli_fetch_all($query, MYSQLI_ASSOC);

		foreach ($projectsArray as $k => $val) {
			$projects[] = $val['project'];
		}


	} else {
		$error = mysqli_error($connect);
		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
		print($page);
		exit();
	}

	$sql = "
	SELECT `id` FROM `user`
	WHERE `email` = '$emailSID'
	";

	$query = mysqli_query($connect, $sql);
	if ($query) {
		$userID = mysqli_fetch_array($query, MYSQLI_NUM);
	} else {
		$error = mysqli_error($connect);
		$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
		print($page);
		exit();
	}

	// отправка формы

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// добавление задачи
		if (isset($_POST['taskSubmit'])) {

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
				if (!(date("d.m.Y", strtotime($val)) == $val) && !(date("d-m-Y", strtotime($val)) == $val))
				{
			    $errors[$k] = 'неверный формат';
			  }
			}
		}

		if (count($errors)) {
			$add = true;
			$taskModal = true;
			$modal = includeTemplate('templates/modal.php', 
			[
				'projects' => $projects,
				'errors' => $errors,
				'values' => $values,
				'taskModal' => $taskModal,
			]);

		} else {

				if (isset($_FILES['preview']['name'])) {
					$fileName = $_FILES['preview']['name'];
					$fileTempPath = $_FILES['preview']['tmp_name'];
					$filePath = __DIR__.'/'.$fileName;
					move_uploaded_file($fileTempPath, $filePath);
				} else {
					$fileName = NULL;
				}

				$sql = "
				SELECT `projects`.`id` FROM `projects`
				JOIN `user`
				ON `id_user` = `user`.`id`
				WHERE	`email` = '$emailSID' && `project` = '$projectNew'
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

				$dateNewTS = strtotime($dateNew);
				$dateNew = date('Y.m.d', $dateNewTS);

				$nameNew = mysqli_real_escape_string($connect, $nameNew);

				$sql = "
				INSERT INTO `tasks` SET
				`create_date` = NOW(),
				`task` = '$nameNew',
				`dateDeadline` = '$dateNew',
				`id_project` = '$projectID[0]',
				`done` = '0',
				`file` = '$fileName',
				`id_user` = '$userID[0]'
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

		}

	// добавление проекта
		if (isset($_POST['projectSubmit'])) {

			$name = $_POST['name'] ?? '';

			if ($name == '') {
				$error = 'заполните это поле';
				$add = true;
				$projectModal = true;
				$modal = includeTemplate('templates/modal.php', 
				[
					'projects' => $projects,
					'error' => $error,
					'values' => $values,
					'projectModal' => $projectModal
				]);
			} else {

				$name = mysqli_real_escape_string($connect, $name);

				$sql = "
				INSERT INTO `projects` SET
				`project` = '$name',
				`id_user` = '$userID[0]'
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
		}
	}


// методы GET

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


if (isset($_GET['today'])) {
	foreach ($tasks as $k => $val) {
		$task_deadline_ts = strtotime($val['dateDeadline']);
		$days_until_deadline = floor(($task_deadline_ts - $current_ts)/86400);
		if (!($days_until_deadline == 0)) {
			unset($tasksSelect[$k]);
		}

	}
}

if (isset($_GET['tomorrow'])) {
	foreach ($tasks as $k => $val) {
		$task_deadline_ts = strtotime($val['dateDeadline']);
		$days_until_deadline = floor(($task_deadline_ts - $current_ts)/86400);
		if (!($days_until_deadline == 1)) {
			unset($tasksSelect[$k]);
		}
	}
}

if (isset($_GET['overdue'])) {
	foreach ($tasks as $k => $val) {

		$task_deadline_ts = strtotime($val['dateDeadline']);
		$days_until_deadline = floor(($task_deadline_ts - $current_ts)/86400);
		if (!($days_until_deadline < 0)) {
			unset($tasksSelect[$k]);
		}
	}
}


$userId = $_GET['done'] ?? false;

if ($userId) {

	$sql = "
	UPDATE `tasks` SET
	`done` = IF (`done` = 0, 1, 0),
	`done_date` = IF (`done` = 1, NOW(), NULL)
	WHERE `id` = $userId
	";

	$query = mysqli_query($connect, $sql);
	if ($query) {
		header('Location: /index.php');
	} else {
		$error = mysqli_error($connect);
		$page = includeTemplate('templates/error.php', ['error' => $error]);
		print($page);
		exit();
	}
}

$addVal = $_GET['add'] ?? '';

if (isset($_GET['add'])) {
	$add = true;
	if ($addVal == 'projectModal') {
		$projectModal = true;
	}
	if ($addVal == 'taskModal') {
		$taskModal = true;
	}
	$modal = includeTemplate('templates/modal.php', 
	[
		'projects' => $projects,
		'taskModal' => $taskModal,
		'projectModal' => $projectModal
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
			if ($val['done'] == 1) {
				unset($tasksSelect[$k]);
			}
		}
	}
} else {
		$checked = '';
		$show_completed = 1;
		foreach ($tasksSelect as $k => $val) {
			if ($val['done'] == 1) {
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

// залогиниться
		if (isset($_POST['loginSubmit'])) {

		$required = ['email', 'password'];
		$errors = [];
		$values = [];

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
// зарегиться
	if (isset($_POST['registerSubmit'])) {

			$required = ['email', 'password', 'name'];
			$filter = ['email'];
			$errors = [];
			$values = [];

		  $nameUser = $_POST['name'] ?? '';
		  $emailUser = $_POST['email'] ?? '';
		  $passwordUser = $_POST['password'] ?? '';

			foreach ($_POST as $k => $val) {

				$values = $_POST;

				if (in_array($k, $required) && $val == '') {
					$errors[$k] = 'это поле требуется заполнить';
				} else if (in_array($k, $filter)) {

					$filtered = filter_var($val, FILTER_VALIDATE_EMAIL);
					if ($filtered) {
							foreach ($users as $val) {
								if ($emailUser == $val['email']) {
									$errors[$k] = 'такой email уже существует';
								}
							}
					} else {
						$errors[$k] = 'неверный формат email';
					}
				}
				
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

				$nameUser = mysqli_real_escape_string($connect, $nameUser);

				$sql = "
				INSERT INTO `user`
				(`reg_date`, `email`, `name`, `password`)
				VALUES
				(NOW(), '$emailUser', '$nameUser', '$passwordHash')
				";

				$query = mysqli_query($connect, $sql);

				if ($query) {

					$sql = "
					SELECT `id` FROM `user`
					WHERE `email` = '$emailUser'
					";
					$query = mysqli_query($connect, $sql);
					$currentId = mysqli_fetch_array($query, MYSQLI_NUM);

					$sql = "
					INSERT INTO `projects` SET
					`project` = 'Все',
					`id_user` = '$currentId[0]'
					";
					$query1 = mysqli_query($connect, $sql);
					if (!$query1) {
						$error = mysqli_error($connect);
						$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
						print($page);
						exit();
					}

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

