<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_fullwidthtitle_info()
{
	return array(
		'name' => tra('Set a Full-Width Page Title'),
		'description' => tra('Allows for the setting of a Page Title that bleeds to the edges of the screen'),
		'documentation' => tra('PluginFullWidthTitle'),
		'default' => 'y',
		'format' => 'html',
		'filter' => 'wikicontent',
		'tags' => array('advanced'),
		'params' => array(
			'title' => array(
				'name' => tr('Page title'),
				'description' => tr('If you need to include tpl files.'),
				'required' => true,
				'filter' => 'text'
			),
			'iconsrc' => array(
				'name' => tr('Icon Source'),
				'description' => tr('Source path of the icon.'),
				'required' => false,
				'filter' => 'text'
			),
		),
	);
}

function wikiplugin_fullwidthtitle($data, $params)
{
	global $smarty;

	$smarty->assign('title', $params['title']);
	if (!empty($params['iconsrc'])) {
		$smarty->assign('iconsrc', $params['iconsrc']);
	}
	return $smarty->fetch('templates/wiki-plugins/wikiplugin_fullwidthtitle.tpl');
}
