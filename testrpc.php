<?php # $Header: /cvsroot/tikiwiki/tiki/testrpc.php,v 1.4 2003-04-24 14:07:57 lrargerich Exp $

include_once("lib/xmlrpc.inc");
include_once("lib/xmlrpcs.inc");

// EDIT FROM THIS LINE
$server_port=80;
$server_uri="localhost";
$server_path="/tcvs/tiki/xmlrpc.php";
// DON'T EDIT BELOW THIS LINE
$client = new xmlrpc_client("$server_path", "$server_uri", $server_port);
$client->setDebug(1);


$appkey='';
$username='admin';
$password='pepe';
/*
$blogs=new xmlrpcmsg('blogger.newPost',array(new xmlrpcval($appkey,"string"),
                                          new xmlrpcval("1","string"),
                                          new xmlrpcval($username,"string"),
                                          new xmlrpcval($password,"string"),
                                          new xmlrpcval("pepe","string"),
                                          new xmlrpcval(0,"boolean"),
                                          ));
*/                            

// Introspection mechanism
$blogs=new xmlrpcmsg('system.listMethods',"");
        
$result=$client->send($blogs);

if(!$result) {
  $errorMsg='Cannot send message to server maybe the server is down';
} else {
  if(!$result->faultCode()) {
    $blogs=xmlrpc_decode($result->value());
    print_r($blogs);
  } else {
    $errorMsg=$result->faultstring();
    print("Error: $errioMsg<br/>");
  }
}


?>