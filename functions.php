<?php 

function countTasks($tasks, $nameProject) {
	if ($nameProject == 'Все') {
		return count($tasks);
	} else {
		$amount = 0;
		foreach ($tasks as $k => $val) {
			if ($val['project'] == $nameProject) {
				$amount++;
			}
		}
		return $amount;
	}
}


function includeTemplate($path, $data) {

	if (file_exists($path)) {

		ob_start();

		extract($data);
		require_once($path);

		$htmlTemplate = ob_get_clean();
		
		return $htmlTemplate;

	}

	return '';
}


function searchUserByEmail($email, $users) {

  foreach ($users as $val) {
    if ($val['email'] == $email) {
        return $val;
    }
  }

  return false;
}

function showError($connect) {
	$error = mysqli_error($connect);
	$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	print($page);
	exit();
}


function countDays($ts, $date) {

	if (isset($date)) {

		$task_deadline_ts = strtotime($date);
		$days_until_deadline = floor(($task_deadline_ts - $ts)/86400);
		return $days_until_deadline;

	} else {
		return false;
	}


}







