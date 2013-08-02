<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once('lib/wiki-plugins/wikiplugin_flash.php');

function wikiplugin_vimeo_info()
{
	global $prefs;

	return array(
		'name' => tra('Vimeo'),
		'documentation' => 'PluginVimeo',
		'description' => tra('Display a Vimeo video'),
		'prefs' => array( 'wikiplugin_vimeo' ),
		'icon' => 'img/icons/vimeo.png',
		'introduced' => 6.1,
		'params' => array(
			'url' => array(
				'required' => $prefs['vimeo_upload'] !== 'y',
				'name' => tra('URL'),
				'description' => tra('Entire URL to the Vimeo video. Example: http://vimeo.com/3319966') .
								($prefs['vimeo_upload'] === 'y' ? ' ' . tra('or leave blank to upload one.') : ''),
				'filter' => 'url',
				'default' => '',
			),
			'width' => array(
				'required' => false,
				'name' => tra('Width'),
				'description' => tra('Width in pixels'),
				'filter' => 'digits',
				'default' => 425,
			),
			'height' => array(
				'required' => false,
				'name' => tra('Height'),
				'description' => tra('Height in pixels'),
				'filter' => 'digits',
				'default' => 350,
			),
			'quality' => array(
				'required' => false,
				'name' => tra('Quality'),
				'description' => tra('Quality of the video'),
				'filter' => 'alpha',
    			'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('High'), 'value' => 'high'), 
					array('text' => tra('Medium'), 'value' => 'medium'), 
					array('text' => tra('Low'), 'value' => 'low'), 
				),
				'default' => 'high',
				'advanced' => true				
			),
			'allowFullScreen' => array(
				'required' => false,
				'name' => tra('Full screen'),
				'description' => tra('Expand to full screen'),
				'filter' => 'alpha',
    			'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Yes'), 'value' => 'true'), 
					array('text' => tra('No'), 'value' => 'false'), 
				),
				'default' => '',
				'advanced' => true				
			),
		),
	);
}

function wikiplugin_vimeo($data, $params)
{
	static $instance = 0;
	$instance++;

	if (isset($params['url'])) {
		$params['vimeo'] = $params['url'];
		unset($params['url']);
		return wikiplugin_flash($data, $params);
	} else {

		global $access, $page;
		$access->check_feature('vimeo_upload');

		// set up for an upload
		$smarty = TikiLib::lib('smarty');
		$smarty->loadPlugin('smarty_function_button');
		$smarty->loadPlugin('smarty_function_service');
		$html = smarty_function_button(array(
			'_keepall' => 'y',
			'_class' => 'vimeo dialog',
			'href' => smarty_function_service(
				array(
					'controller' => 'vimeo',
					'action' => 'upload',
				),
				$smarty
			),
			'_text' => tra('Upload Video'),
		), $smarty);

		TikiLib::lib('header')->add_jq_onready('
$(".vimeo.dialog").click(function () {
	var link = this;
	$(this).serviceDialog({
		title: tr("Upload Video"),
		data: {
			controller: "vimeo",
			action: "upload"
		},
		load: function(data) {
			var $dialog = $(".vimeo_upload").parents(".ui-dialog-content");		// odd its the content, not the outer div
			$(".vimeo_upload").on("vimeo_uploaded", function(event, data) {
				var params = {
					page: ' . json_encode($page) . ',
					content: "",
					index: ' . $instance . ',
					type: "vimeo",
					params: {
						url: data.url
					}
				};
				$.post("tiki-wikiplugin_edit.php", params, function() {
					$dialog.dialog("destroy").remove();
					$.get($.service("wiki", "get_page", {page:' . json_encode($page) . '}), function (data) {
						if (data) {
							$("#page-data").html(data);
						}
					});
				});

			});
		}
	});
	return false;
});');

		return $html;
	}

}
