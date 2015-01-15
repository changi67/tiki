<?php

function prefs_https_list() {
	return array(
		'https_external_links_for_users' => array(
			'name' => tra('Use HTTPS when building user-specific links'),
			'description' => tra('When building notification emails, RSS feeds or other externally available links, use HTTPS when the content applies to a specific user. HTTPS must be configured on the server.'),
			'type' => 'flag',
		),
		'https_port' => array(
			'name' => tra('HTTPS port'),
			'type' => 'text',
			'size' => 5,
			'filter' => 'digits',
		),
		'https_login' => array(
			'name' => tra('Use HTTPS login'),
			'description' => tra('Increase security by allowing to transmit authentication credentials over SSL. Certificates must be configured on the server.'),
			'type' => 'list',
			'options' => array(
				'disabled' => tra('Disabled'),
				'allowed' => tra('Allow secure (https) login'),
				'encouraged' => tra('Encourage secure (https) login'),
				'force_nocheck' => tra('Consider we are always in HTTPS, but do not check'),
				'required' => tra('Require secure (https) login'),
			),
		),
	);
}