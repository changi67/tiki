<?php

// Displays the user Avatar
// Use:
// {AVATAR()}username{AVATAR}
//  (page=>some)         Avatar is a link to "some"
//  (float=>left|right)  Avatar is floated to left or right
//
// If no avatar nothing is displayed

function wikiplugin_avatar_help() {
  return tra("Displays the user Avatar");
}

function wikiplugin_avatar($data,$params) {
  global $tikilib;
  global $userlib;
  
  extract($params);

  if (isset($float)) $avatar = $tikilib->get_user_avatar( $data, $float );
  else               $avatar = $tikilib->get_user_avatar( $data );

  if (isset($page)) {
    $avatar = "<a href='tiki-index.php?page=$page'>".$avatar.'</a>';
  } else if ($userlib->user_exists($data) &&
             $tikilib->get_user_preference($data,'user_information','public')=='public') {
    $avatar = "<a href='tiki-user_information.php?view_user=$data'>".$avatar.'</a>';
  } 
  
  return $avatar;
}


?>
