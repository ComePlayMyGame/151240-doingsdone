<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
	showError($connect);
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
			showError($connect);
		}


	$sql = "
	SELECT `projects`.`project`, `projects`.`id` FROM `projects`
	JOIN `user`
	ON `id_user` = `user`.`id`
	WHERE	`email` = '$emailSID'
	";

	$query = mysqli_query($connect, $sql);
	if ($query) {	
		$projects = mysqli_fetch_all($query, MYSQLI_ASSOC);
	} else {
		showError($connect);
	}

	$sql = "
	SELECT `id` FROM `user`
	WHERE `email` = '$emailSID'
	";

	$query = mysqli_query($connect, $sql);
	if ($query) {
		$userID = mysqli_fetch_array($query, MYSQLI_NUM);
	} else {
		showError($connect);
	}


	$addVal = $_GET['add'] ?? '';

	if (isset($_GET['add'])) {
		$add = true;
		$projectModal = ($addVal == 'projectModal') ? true : false;
		$taskModal = ($addVal == 'taskModal') ? true : false;
		$modal = includeTemplate('templates/modal.php', 
		[
			'projects' => $projects,
			'taskModal' => $taskModal,
			'projectModal' => $projectModal
		]);
	} else {
		$add = false;
		$modal = false;
	}



	// отправка формы

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// добавление задачи
		if (isset($_POST['taskSubmit'])) {

		$required = ['name', 'project'];
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

		$projectNew = mysqli_real_escape_string($connect, $projectNew);		

		$sql = "
		SELECT `projects`.`id` FROM `projects`
		JOIN `user`
		ON `id_user` = `user`.`id`
		WHERE	`email` = '$emailSID' && `projects`.`id` = '$projectNew'
		";
		
		$query = mysqli_query($connect, $sql);
		if ($query) {
			$projectID = mysqli_fetch_array($query, MYSQLI_NUM);
			if (!$projectID) {
				$errors['project'] = 'такой проект отсутствует';
			}
		} else {
			showError($connect);
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


				if (!($dateNew == '')) {
					$dateNewTS = strtotime($dateNew);
					$dateNew = date('Y.m.d', $dateNewTS);
				}

				$nameNew = mysqli_real_escape_string($connect, $nameNew);

				$sql = "
				INSERT INTO `tasks` SET
				`create_date` = NOW(),
				`task` = '$nameNew',
				`dateDeadline` = IF ('$dateNew' = '', NULL, '$dateNew'),
				`id_project` = '$projectNew',
				`done` = '0',
				`file` = '$fileName',
				`id_user` = '$userID[0]'
				";

				$query = mysqli_query($connect, $sql);
				if ($query) {
					header('location: /index.php');
				} else {
					showError($connect);

				}

				$modal = false;
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
					// 'values' => $values,
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
					showError($connect);

				}

				$modal = false;

			}
		}
	}



// выполненные задачи
if (isset($_GET['show_completed'])) {
	$show_completed = $_GET['show_completed'];
	setcookie('showCompleted', $show_completed, strtotime("+30 days"));
	header('Location: /index.php');
}


// куки
$showCompleted = $_COOKIE['showCompleted'] ?? false;

if ($showCompleted) {
	$checked = 'checked';
	$show_completed = 0;
} else {
	$checked = '';
	$show_completed = 1;
	foreach ($tasks as $k => $val) {
		if ($val['done'] == 1) {
			unset($tasks[$k]);
		}
	}
}


// методы GET

$projectKey = $_GET['project'] ?? false;

if ($projectKey) {
	if (count($projects) > $projectKey) {
		foreach ($tasks as $k => $val) {
			if ($val['project'] == $projects[$projectKey]['project']) {
				$tasksSelect[] = $val;
			}
		}
	} else {
		http_response_code(404);
		exit('<b>Ошибка 404</b>');
	}
}
else {
	$tasksSelect = $tasks;
}



if (isset($_GET['today'])) {
	foreach ($tasks as $k => $val) {
			$days_until_deadline = countDays($current_ts, $val['dateDeadline']);
			$today = (float)0;
		if (!($days_until_deadline === $today)) {
			unset($tasksSelect[$k]);
		}
	}
}

if (isset($_GET['tomorrow'])) {
	foreach ($tasks as $k => $val) {
		$days_until_deadline = countDays($current_ts, $val['dateDeadline']);
		if (!($days_until_deadline == 1)) {
			unset($tasksSelect[$k]);
		}
	}
}

if (isset($_GET['overdue'])) {
	foreach ($tasks as $k => $val) {
			$days_until_deadline = countDays($current_ts, $val['dateDeadline']);
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
		showError($connect);
	}
}


$tasksSelect = $tasksSelect ?? '';

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


		$guest = includeTemplate('templates/guest.php', 
		[
			'login' => $login,
			'errors' => $errors,
			'values' => $values
		]);
		print($guest);

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
			$values = $_POST;

			foreach ($_POST as $k => $val) {
				if (in_array($k, $required) && $val == '') {
					$errors[$k] = 'это поле требуется заполнить';
				} else if (in_array($k, $filter)) {
					$filtered = filter_var($val, FILTER_VALIDATE_EMAIL);
					if ($filtered) {
						$sql = "
						SELECT `id` FROM `user`
						WHERE `email` = '$val'
						";
						$query = mysqli_query($connect, $sql);
						if ($query) {
							$emailId = mysqli_fetch_array($query, MYSQLI_NUM);
							if ($emailId[0]) {
								$errors[$k] = 'такой email уже существует';
							}
						} else {
							showError($connect);
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
				exit();

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
					$sql2 = "
					INSERT INTO `projects` SET
					`project` = 'Входящие',
					`id_user` = '$currentId[0]'
					";
					$query = mysqli_query($connect, $sql);
					$query2 = mysqli_query($connect, $sql2);
					if (!$query || !$query2) {
						showError($connect);
					}

					header('location: /index.php?login');
				} else {
					showError($connect);

				}

			}

	}


	}

	if (isset($_GET['login'])) {
		$login = true;
	} else if (isset($_GET['register'])) {
		print(includeTemplate('templates/register.php', []));
		exit();
	} else {
		$login = false;
	}


	$guest = includeTemplate('templates/guest.php', 
	[
		'login' => $login,
	]);
	print($guest);

}

