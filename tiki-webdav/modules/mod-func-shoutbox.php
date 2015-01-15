<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/*
 * AJAXified Shoutbox module (jonnybradley for mpvolt Aug/Sept 2008)
 * 
 * Prefers Ajax enabled (Admin/Features/Experimental - feature_ajax) but will work the old way without it
 * Anonymous may need tiki_p_view_shoutbox and tiki_p_post_shoutbox setting (in Group admin)
 * Enable Admin/Wiki/Wiki Features/feature_antibot to prevent spam ("Anonymous editors must input anti-bot code")
 * 
 */

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'],basename(__FILE__)) !== false) {
  header('location: index.php');
  exit;
}

function module_shoutbox_info() {
	return array(
		'name' => tra('Shoutbox'),
		'description' => tra('The shoutbox is a quick messaging tool. Messages reload each time the page changes. Anyone with the right permission can see all messages. Another permission allows to send messages.'),
		'prefs' => array( 'feature_shoutbox' ),
		'params' => array(
			'tooltip' => array(
				'name' => tra('Tooltip'),
				'description' => tra('If set to "1", displays message post dates and times as tooltips instead of showing directly in the module content.') . " " . tr('Default:') . '"0".',
				'filter' => 'word'
			),
			'buttontext' => array(
				'name' => tra('Button label'),
				'description' => tra('Label on the button to post a message.') . " " . tra('Default:') . " " . tra("Post")
			),
			'waittext' => array(
				'name' => tra('Wait label'),
				'description' => tra('Label on the button to post a message when the message is being posted if AJAX is enabled.') . " " . tra('Default:') . " " . tra("Please wait...")
			),
			'maxrows' => array(
				'name' => tra('Maximum messages shown'),
				'description' => tra('Number of messages to display.') . ' ' . tra('Default:') . ' 5.',
				'filter' => 'int'
			)
		)
	);
}

function doProcessShout($inFormValues) {
	global $shoutboxlib, $user, $smarty, $prefs;
	
	if (array_key_exists('shout_msg',$inFormValues) && strlen($inFormValues['shout_msg']) > 2) {
		if (empty($user) && $prefs['feature_antibot'] == 'y' && (!isset($_SESSION['random_number']) || $_SESSION['random_number'] != $inFormValues['antibotcode'])) {
			$smarty->assign('shout_error', tra('You have mistyped the anti-bot verification code; please try again.'));
			$smarty->assign_by_ref('shout_msg', $inFormValues['shout_msg']);
		} else {
			$shoutboxlib->replace_shoutbox(0, $user, $inFormValues['shout_msg']);
		}
	}
}

function module_shoutbox( $mod_reference, $module_params ) {
	global $tikilib; require_once ('lib/tikilib.php');
	global $shoutboxlib, $prefs, $user, $tiki_p_view_shoutbox, $tiki_p_admin_shoutbox, $tiki_p_post_shoutbox, $base_url, $smarty;
	include_once ('lib/shoutbox/shoutboxlib.php');

	if ($tiki_p_view_shoutbox == 'y') {
		if ($prefs['feature_ajax'] != 'y') {
			$setup_parsed_uri = parse_url($_SERVER['REQUEST_URI']);
	
			if (isset($setup_parsed_uri['query'])) {
				TikiLib::parse_str($setup_parsed_uri['query'], $sht_query);
			} else {
				$sht_query = array();
			}
		
			$shout_father = $setup_parsed_uri['path'];
		
			if (isset($sht_query) && count($sht_query) > 0) {
				$sht = array();
				foreach ($sht_query as $sht_name => $sht_val) {
					$sht[] = $sht_name . '=' . $sht_val;
				}
				$shout_father.= '?'.implode('&amp;',$sht).'&amp;';
			} else {
				$shout_father.= '?';
			}
		} else {	// $prefs['feature_ajax'] == 'y'
			$shout_father = 'tiki-shoutbox.php?';
			global $ajaxlib;
			require_once('lib/ajax/ajaxlib.php');
		}
	
		$smarty->assign('shout_ownurl', $shout_father);
		if (isset($_REQUEST['shout_remove'])) {
			$info = $shoutboxlib->get_shoutbox($_REQUEST['shout_remove']);
			if ($tiki_p_admin_shoutbox == 'y'  || $info['user'] == $user ) {
				if ($prefs['feature_ticketlib2'] =='y') {
					$area = 'delshoutboxentry';
					if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
						key_check($area);
						$shoutboxlib->remove_shoutbox($_REQUEST["shout_remove"]);
					} else {
						key_get($area);
					}
				} else {
					$shoutboxlib->remove_shoutbox($_REQUEST["shout_remove"]);
				}
			}
		}
	
		if ($tiki_p_post_shoutbox == 'y') {
			if ($prefs['feature_ajax'] == 'y') {
				if (!isset($_REQUEST['xajax'])) {	// xajaxRequestUri needs to be set to tiki-shoutbox.php in JS before calling the func
					$ajaxlib->registerFunction('processShout');
				}
			} else {
				if (isset($_REQUEST['shout_send'])) {
					doProcessShout($_REQUEST);
				}
			}
		}
	
		$maxrows = isset($module_params['maxrows']) ? $module_params['maxrows'] : 5;
		$shout_msgs = $shoutboxlib->list_shoutbox(0, $maxrows, 'timestamp_desc', '');
		$smarty->assign('shout_msgs', $shout_msgs['data']);
	
		// Subst module parameters
		$smarty->assign('tooltip', isset($module_params['tooltip']) ? $module_params['tooltip'] : 0);
		$smarty->assign('buttontext', isset($module_params['buttontext']) ? $module_params['buttontext'] : tra('Post'));
		$smarty->assign('waittext', isset($module_params['waittext']) ? $module_params['waittext'] : tra('Please wait...'));
		
		if ($prefs['feature_ajax'] == 'y') {
			if (!isset($_REQUEST['xajax'])) {
				$ajaxlib->registerTemplate('mod-shoutbox.tpl');
			}
		}
	}
}