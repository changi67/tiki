<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

global $ranklib; include_once ('lib/rankings/ranklib.php');
$categs=$ranklib->get_jail();
$ranking = $ranklib->wiki_ranking_top_pages($module_rows, $categs ? $categs : array());

$smarty->assign('modTopPages', $ranking["data"]);
$smarty->assign('nonums', isset($module_params["nonums"]) ? $module_params["nonums"] : 'n');
$smarty->assign('module_rows', $module_rows);
