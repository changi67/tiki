<?php
include('db/local.php');
$ADODB_SESSION_DRIVER=$db_tiki;
$ADODB_SESSION_CONNECT=$host_tiki;
$ADODB_SESSION_USER=$user_tiki;
$ADODB_SESSION_PWD=$pass_tiki;
$ADODB_SESSION_DB=$dbs_tiki;
unset($db_tiki);
unset($host_tiki);
unset($user_tiki);
unset($pass_tiki);
unset($dbs_tiki);
ini_set('session.save_handler','user');
include_once('lib/adodb/session/adodb-session.php');