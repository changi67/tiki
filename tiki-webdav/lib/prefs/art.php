<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_art_list() {
	return array(
		'art_home_title' => array(
			'name' => tra('Title of articles home page'),
			'type' => 'list',
			'options' => array(
				'' => '',
				'topic' => tra('Topic'),
				'type' => tra('Type'),
				'articles' => tra('Articles'),
			),
		),
		'art_list_title' => array(
			'name' => tra('Title'),
			'type' => 'flag',
		),
		'art_list_title_len' => array(
			'name' => tra('Title length'),
			'type' => 'text',
			'size' => '5',
			'filter' => 'digits',
		),
		'art_list_type' => array(
			'name' => tra('Type'),
			'type' => 'flag',
		),
		'art_list_topic' => array(
			'name' => tra('Topic'),
			'type' => 'flag',
		),
		'art_list_date' => array(
			'name' => tra('Publication date'),
			'type' => 'flag',
		),
		'art_list_expire' => array(
			'name' => tra('Expiration date'),
			'type' => 'flag',
		),
		'art_list_visible' => array(
			'name' => tra('Visible'),
			'type' => 'flag',
		),
		'art_list_lang' => array(
			'name' => tra('Language'),
			'type' => 'flag',
		),
		'art_list_author' => array(
			'name' => tra('Author'),
			'type' => 'flag',
		),
		'art_list_rating' => array(
			'name' => tra('Rating'),
			'type' => 'flag',
		),
		'art_list_reads' => array(
			'name' => tra('Reads'),
			'type' => 'flag',
		),
		'art_list_size' => array(
			'name' => tra('Size'),
			'type' => 'flag',
		),
		'art_list_img' => array(
			'name' => tra('Images'),
			'type' => 'flag',
		),
		'art_list_id' => array(
			'name' => tra('Id'),
			'type' => 'flag',
		),
	);
}