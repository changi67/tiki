<?php
// (c) Copyright 2002-2015 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_objectlink_info()
{
	return array(
		'name' => tra('Object Link'),
		'description' => tra('Displays a link to an object in the system.'),
		'prefs' => array('wikiplugin_objectlink'),
		'icon' => 'img/icons/world_link.png',
		'tags' => array( 'basic' ),
		'format' => 'html',
		'inline' => true,
		'params' => array(
			'type' => array(
				'required' => true,
				'name' => tr('Type'),
				'description' => tr('The object type'),
				'accepted' => 'wiki, user, external, relation_source, relation_target, freetag, trackeritem',
				'type' => 'text',
			),
			'id' => array(
				'required' => true,
				'name' => tra('Object ID'),
				'description' => tra('The item to display'),
				'filter' => 'text',
				'profile_reference' => 'type_in_param',
			),
		),
	);
}

function wikiplugin_objectlink($data, $params)
{
	$smarty = TikiLib::lib('smarty');
	$smarty->loadPlugin('smarty_function_object_link');

	return smarty_function_object_link(
		array(
			'type' => $params['type'],
			'id' => $params['id'],
		),
		$smarty
	);
}

