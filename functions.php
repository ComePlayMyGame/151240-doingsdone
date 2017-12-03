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





