<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

function smarty_function_redirect($params, &$smarty) {
	extract($params, EXTR_SKIP);
	// Param = url
	if(empty($url)) {
		$smarty->trigger_error("assign: missing parameter: url");
	return;
	}
	header("Location: $url");
	exit;
}



?>