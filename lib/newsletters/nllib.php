<?php
 
class NlLib extends TikiLib {
  
  function NlLib($db) 
  {
    if(!$db) {
      die("Invalid db object passed to UsersLib constructor");  
    }
    $this->db = $db;  
  }
   
  function replace_newsletter($nlId,$name,$description,$allowAnySub,$frequency)
  {
    $name = addslashes($name);
    $description = addslashes($description);
    if($nlId) {
      // update an existing quiz
      $query = "update tiki_newsletters set 
      name = '$name',
      description = '$description',
      allowAnySub = '$allowAnySub',
      frequency = $frequency
      where nlId = $nlId";
      $result = $this->db->query($query);
      if(DB::isError($result)) $this->sql_error($query, $result);
    } else {
      // insert a new quiz
      $now = date("U");
      $query = "insert into tiki_newsletters(name,description,allowAnySub,frequency,lastSent,editions,users,created)
      values('$name','$description','$allowAnySub',$frequency,$now,0,0,$now)";
      $result = $this->db->query($query);
      if(DB::isError($result)) $this->sql_error($query, $result);
      $queryid = "select max(nlId) from tiki_newsletters where created=$now";
      $nlId = $this->db->getOne($queryid);  
    }
    return $nlId;
  }
  
  function replace_edition($nlId,$subject,$data,$users)
  {
    $subject = addslashes($subject);
    $data = addslashes($data);
    // insert a new quiz
    $now = date("U");
    $query = "insert into tiki_sent_newsletters(nlId,subject,data,sent,users)
    values($nlId,'$subject','$data',$now,$users)";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
  }
  
  function get_subscribers($nlId) 
  {
    $query = "select email from tiki_newsletter_subscriptions where valid='y' and nlId=$nlId";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $ret = Array();
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $ret[]=$res["email"];
    }
    return $ret;
  }

  function remove_newsletter_subscription($nlId,$email)
  {
    $valid = $this->db->getOne("select valid from tiki_newsletter_subscriptions where nlId=$nlId and email='$email'");	
    
    $query = "delete from tiki_newsletter_subscriptions where nlId=$nlId and email='$email'";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);	
    $this->update_users($nlId);    
  }

  function newsletter_subscribe($nlId,$email) 
  {
    global $smarty;
    global $user;
    $email=addslashes($email);  	 
    // Generate a code and store it and send an email with the
    // URL to confirm the subscription put valid as 'n'
    $foo = parse_url($_SERVER["REQUEST_URI"]);
    $url_subscribe = httpPrefix().$foo["path"];
    $code = md5($this->tikilib->genPass());
    $now = date("U");
    $query = "replace into tiki_newsletter_subscriptions(nlId,email,code,valid,subscribed)
    values($nlId,'$email','$code','n',$now)";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $info = $this->get_newsletter($nlId);
    $smarty->assign('info',$info);
    // Now send an email to the address with the confirmation instructions
    $smarty->assign('mail_date',date("U"));
    $smarty->assign('mail_user',$user);
    $smarty->assign('code',$code);
    $smarty->assign('url_subscribe',$url_subscribe);
    $smarty->assign('server_name',$_SERVER["SERVER_NAME"]);
    $mail_data=$smarty->fetch('mail/confirm_newsletter_subscription.tpl');
    @mail($email, tra('Newsletter subscription information at ').$_SERVER["SERVER_NAME"],$mail_data);
    $this->update_users($nlId);    
  }
  
  function confirm_subscription($code)
  {
    global $smarty;
    global $user;
    $foo = parse_url($_SERVER["REQUEST_URI"]);
    $url_subscribe = httpPrefix().$foo["path"];       	 
    $query = "select * from tiki_newsletter_subscriptions where code='$code'";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    if(!$result->numRows()) return false;
    $res = $result->fetchRow(DB_FETCHMODE_ASSOC);
    $info = $this->get_newsletter($res["nlId"]);
    $smarty->assign('info',$info);
    $query = "update tiki_newsletter_subscriptions set valid='y' where code='$code'";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    // Now send a welcome email
    $smarty->assign('mail_date',date("U"));
    $smarty->assign('mail_user',$user);
    $smarty->assign('code',$res["code"]);
    $smarty->assign('url_subscribe',$url_subscribe);
    $mail_data=$smarty->fetch('mail/newsletter_welcome.tpl');
    @mail($res["email"], tra('Welcome to ').$info["name"].tra(' at ').$_SERVER["SERVER_NAME"],$mail_data);
    return $this->get_newsletter($res["nlId"]);
  }
  
  function unsubscribe($code)
  {
    global $smarty;
    global $user;
    $foo = parse_url($_SERVER["REQUEST_URI"]);
    $url_subscribe = httpPrefix().$foo["path"];
    $query = "select * from tiki_newsletter_subscriptions where code='$code'";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    if(!$result->numRows()) return false;
    $res = $result->fetchRow(DB_FETCHMODE_ASSOC);
    $info = $this->get_newsletter($res["nlId"]);
    $smarty->assign('info',$info);
    $smarty->assign('code',$res["code"]);
    $query = "delete from tiki_newsletter_subscriptions where code='$code'";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    // Now send a bye bye email
    $smarty->assign('mail_date',date("U"));
    $smarty->assign('mail_user',$user);
    $smarty->assign('url_subscribe',$url_subscribe);
    $mail_data=$smarty->fetch('mail/newsletter_byebye.tpl');
    @mail($res["email"], tra('Bye bye from ').$info["name"].tra(' at ').$_SERVER["SERVER_NAME"],$mail_data);
    $this->update_users($res["nlId"]);    
    return $this->get_newsletter($res["nlId"]);
  }
  
  function add_all_users($nlId)
  {
    $query = "select email from users_users";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $email = $res["email"];
      if(!empty($email)) {
        $this->newsletter_subscribe($nlId,$email);  
      }
    }
  }
  
  function get_newsletter($nlId) 
  {
    $query = "select * from tiki_newsletters where nlId=$nlId";
    $result = $this->db->query($query);
    if(!$result->numRows()) return false;
    if(DB::isError($result)) $this->sql_error($query, $result);
    $res = $result->fetchRow(DB_FETCHMODE_ASSOC);
    return $res;
  }
  
  function get_edition($editionId) 
  {
    $query = "select * from tiki_sent_newsletters where editionId=$editionId";
    $result = $this->db->query($query);
    if(!$result->numRows()) return false;
    if(DB::isError($result)) $this->sql_error($query, $result);
    $res = $result->fetchRow(DB_FETCHMODE_ASSOC);
    return $res;
  }

  function update_users($nlId) 
  {
    $users = $this->db->getOne("select count(*) from tiki_newsletter_subscriptions where nlId=$nlId");
    $query = "update tiki_newsletters set users=$users where nlId=$nlId";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
  }  
   
  function list_newsletters($offset,$maxRecords,$sort_mode,$find)
  {
    $sort_mode = str_replace("_"," ",$sort_mode);
    if($find) {
    $mid=" where (name like '%".$find."%' or description like '%".$find."%')";  
    } else {
      $mid=" "; 
    }
    $query = "select * from tiki_newsletters $mid order by $sort_mode limit $offset,$maxRecords";
    $query_cant = "select count(*) from tiki_newsletters $mid";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $cant = $this->db->getOne($query_cant);
    $ret = Array();
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $res["confirmed"]=$this->db->getOne("select count(*) from tiki_newsletter_subscriptions where valid='y' and nlId=".$res["nlId"]);
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
  
  function list_editions($offset,$maxRecords,$sort_mode,$find)
  {
    $sort_mode = str_replace("_"," ",$sort_mode);
    if($find) {
    $mid=" and (subject like '%".$find."%' or data like '%".$find."%')";  
    } else {
      $mid=" "; 
    }
    $query = "select tsn.editionId,tn.nlId,subject,data,tsn.users,sent,name from tiki_newsletters tn, tiki_sent_newsletters tsn where tn.nlId=tsn.nlId $mid order by $sort_mode limit $offset,$maxRecords";
    $query_cant = "select count(*) from tiki_newsletters tn, tiki_sent_newsletters tsn where tn.nlId=tsn.nlId $mid";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $cant = $this->db->getOne($query_cant);
    $ret = Array();
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
   
  function list_newsletter_subscriptions($nlId,$offset,$maxRecords,$sort_mode,$find)
  {
    $sort_mode = str_replace("_"," ",$sort_mode);
    if($find) {
    $mid=" where nlId=$nlId and (name like '%".$find."%' or description like '%".$find."%')";  
    } else {
      $mid=" where nlId=$nlId "; 
    }
    $query = "select * from tiki_newsletter_subscriptions $mid order by $sort_mode limit $offset,$maxRecords";
    $query_cant = "select count(*) from tiki_newsletter_subscriptions $mid";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $cant = $this->db->getOne($query_cant);
    $ret = Array();
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
    
  function remove_newsletter($nlId)
  {
    $query = "delete from tiki_newsletters where nlId=$nlId";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $query = "delete from tiki_newsletter_subscriptions where nlId=$nlId";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
    $this->tikilib->remove_object('newsletter',$nlId);
    return true;    
  }
  
  function remove_edition($editionId)
  {
    $query = "delete from tiki_sent_newsletters where editionId=$editionId";
    $result = $this->db->query($query);
    if(DB::isError($result)) $this->sql_error($query, $result);
  }
  
  // Now functions to add/remove/replace/list email addresses from the list of subscriptors
  
  
}

$nllib= new NlLib($dbTiki);
?>