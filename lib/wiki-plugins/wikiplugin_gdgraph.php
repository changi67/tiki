<?php
// (c) Copyright 2002-2014 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// plugin that uses lib/graph-engine/ to produce simple graphs on screen
// Usage
// {GDGRAPH(various parameters)}
//  x y data
// {GDGRAPH}


function wikiplugin_gdgraph_info()
{
	return array(
		'name' => tra('GDGraph'),
		'documentation' => 'PluginGDGraph',
		'description' => tra('Creates a simple graph from supplied data'),
		'tags' => array('basic'),
		'prefs' => array('wikiplugin_gdgraph'),
		'body' => tra('x y data to be graphed ie space separated'),
		'icon' => 'img/icons/chart_curve.png',
		'format' => 'html',
		'params' => array(
			'type' => array(
				'required' => true,
				'name' => tra('Graph type'),
				'description' => tra('Graph type must be set: use either barvert, barhoriz, multiline or pie (but multiline and pie are not yet working'),
				'filter' => 'word',
				'options' => array(
					array('text' => tra('Vertical Bar'), 'value' => 'barvert'),
					array('text' => tra('Horizonatal Bar'), 'value' => 'barhoriz'),
					array('text' => tra('Multiline'), 'value' => 'multiline'),
					array('text' => tra('Pie'), 'value' => 'pie'),
				),
			),
			'title' => array(
				'required' => false,
				'name' => tra('Graph title'),
				'description' => tra('Displayed above the graph'),
				'filter' => 'text',
				'default' => '',
			),
			'bg' => array(
				'required' => false,
				'name' => tra('Background color'),
				'description' => tra('As defined by CSS, name or Hex code - not used yet'),
				'filter' => 'text',
				'default' => '',
			),
			'width' => array(
				'required' => false,
				'name' => tra('Graph image width'),
				'description' => tra('overall width in pixels. Default value is 300.'),
				'filter' => 'digits',
				'default' => 300,
			),
			'height' => array(
				'required' => false,
				'name' => tra('Graph image height'),
				'description' => tra('Height in pixels, auto if not set.'),
				'filter' => 'digits',
				'default' => 0,
			),
			'class' => array(
				'required' => false,
				'name' => tra('CSS Class'),
				'description' => tra('Apply custom CSS class to the surrounding div - not used yet'),
			),
			'id' => array(
				'required' => false,
				'name' => tra('ID'),
				'description' => tra('Apply an id to the surrounding div - not used yet'),
			),
			'float' => array(
				'required' => false,
				'name' => tra('Float Position'),
				'description' => tra('Set the alignment for the div surrounding the graph. For elements with a width of less than 100%, other elements will wrap around it unless the clear parameter is appropriately set - not used yet'),
			),
		),
	);
}

function wikiplugin_gdgraph($data, $params)
{
	// check required param
	if (!isset($params['type']) || ($params['type'] !== 'barvert' && $params['type'] !== 'barhoriz')) {
		return ("<span class='error'>missing or wrong graph type parameter - ony barvert and barhoriz available at present</span>");
	}

	// set default params
	$plugininfo = wikiplugin_gdgraph_info();
	$default = array();
	foreach ($plugininfo['params'] as $key => $param) {
		$default["$key"] = $param['default'];
	}
	$params = array_merge($default, $params);

	// parse the body content to allow data to be generated from other plugins and strip tags
	$data = TikiLib::lib('parser')->parse_data($data, array('noparseplugins' => false, 'suppress_icons' => true));
	// strip tags
	$data = strip_tags($data);

	// split into xy array using a space as the split parameter with x-data as even number indices and y-data odd indices
	$data = explode("\n", $data);
	// remove empties
	$data = array_filter($data);

	$xy = array();
	foreach ($data as $line) {
		$pair = explode(',', $line);
		if (count($pair) !== 2) {
			return "<span class='error'>gdgraph plugin: ERROR: xy data count mismatch - odd dnumber of values</span>";
		}
		$xy[] = $pair;
	}

	if (empty($xy)) {
		return "<span class='error'>gdgraph plugin: ERROR: there must be at least one XY data pair</span>";
	}

	// Set height dynamically if required, somewhat arbitrarily FIXME
	if (empty($params['height'])) {
		if ($params['type'] === 'barhoriz') {
			$params['height'] = count($xy) * 15 + 100;
		} else if ($params['type'] === 'barvert') {
			$params['height'] = count($xy) * 30 + 100;
		} else if (! is_numeric($params['height'])) {
			$params['height'] = 300;		// better than nothing?
		}
	}

// -------------------------------------------------------
// Construct separate XY data strings from the array data to suit the graph-engine libraries.
// The XY data strings should each contain the same number
// of data elements.

	$xydata = array('xdata' => array(), 'ydata' => array());
	for ($i = 0; $i < count($xy); $i++) {
		$xydata['xdata'][] = $xy[$i][0];
		$xydata['ydata'][] = floatval($xy[$i][1]);
	}

	$imgparams = array(
		'type' => $params['type'],
		'title' => $params['title'],
		'height' => $params['height'],
		'width' => $params['width'],
		'usexydata' => json_encode($xydata),
	);

	$ret = '<div class="wp-gdgraph">'.
		'<img src="tiki-gdgraph.php?'. http_build_query($imgparams, '', '&amp;') . '" alt="{tr}GDgraph graph image{/tr}">'.
		'</div>';

	return $ret;

}