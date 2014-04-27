<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
	header('location: index.php');
	exit;
}

/**
 * Class Table_Code_Pager
 *
 * Creates code for the pager section of the Tablesorter code, including the code for ajax
 *
 * @package Tiki
 * @subpackage Table
 * @uses Table_Code_Manager
 */
class Table_Code_Pager extends Table_Code_Manager
{

	public function setCode()
	{
		$p = array();
		$pre = parent::$ajax ? 'pager_' : '';
		//add pager controls
		if (parent::$pager) {
			$p[] = $pre . 'size: ' . parent::$s['pager']['max'];
			if (!parent::$ajax) {
				$p[] = 'container: $(\'div#' . parent::$s['pager']['controls']['id'] . '\')';
				$p[] = 'output: tr(\'Showing \') + \'{startRow} \' + tr(\'to\') + \' {endRow} \' + tr(\'of\')
					+ \' {filteredRows} \' + \'(\' + tr(\'filtered from\') + \' {totalRows} \' + tr(\'records\') + \')\'';
			} else {
				$p[] = $pre . 'output: \'{start} {parens}\'';
			}
		}

		//ajax settings
		if (parent::$ajax) {
			$p[] = $pre . 'ajaxObject: {dataType: \'html\'}';
			$p[] = $pre . 'ajaxUrl : \'' . parent::$s['ajax']['url'] . '\'';
			$p[] = $pre . 'savePages: false';

			//ajax processing - this part grabs the html, usually from the smarty template file
			$ap = array(
				//set variables. parse data into array. data is the html that smarty returns for the entire page
				//returning object r allows custom variables to be available elsewhere in tablesorter under
				//table.config.pager.ajaxData
				//variables tablesorter uses: rows, total and headers; all others are custom
				'var parsedpage = $.parseHTML(data), r = {}, p = table.config.pager;',
				//extract table body rows from html returned by smarty template file
				'r.rows = $(parsedpage).find(\'' . parent::$tid . ' tbody tr\');',
				'r.total = \'' . parent::$s['total'] . '\';',
				'if (r.rows.length > 0) {',
					//fetch number of filtered rows and offset embedded in HTML with hidden input fields added to tpl file
				'	r.filtered = parseInt($(parsedpage).find(\'#' . parent::$s['ajax']['servercount']['id'] . '\').val());',
				'	r.offset = parseInt($(parsedpage).find(\'#' . parent::$s['ajax']['serveroffset']['id'] . '\').val());',
					//set pager text
				'	r.fp = Math.ceil( r.filtered / p.size );',
				'	r.end = r.offset + $(r.rows).length;',
				'	if (r.filtered == 0) {r.start = tr(\'No records found\')}',
				'	if (r.filtered == 1) {r.start = tr(\'Showing 1 of 1\')}',
				'	if (r.filtered > 1) {r.start = tr(\'Showing \') + (r.offset + 1) + \' \' + tr(\'to\') + \' \'
						+ r.end + \' \' + \' \' + tr(\'of\') + \' \' + r.filtered}',
				'	r.parens = r.filtered < r.total ? \' \' + \'(\' + tr(\'filtered from\') + \' \' + r.total + \' \'
					+ tr(\'records)\') : \' \' + tr(\'records\');',
				'} else {',
				'	r.start = tr(\'No records found\');',
				'	r.parens = \'\';',
				'	$(p.$size.selector).addClass(\'disabled\');',
				'}',
				'r.headers = null;',
				//return object
				'return r;'
			);
			$p[] = $this->iterate(
				$ap,
				$pre . 'ajaxProcessing: function(data, table){',
				$this->nt3 . '}',
				$this->nt4,
				'',
				''
			);

			//customAjaxUrl: takes the url parameters generated by Tablesorter and converts to parameters that can
			//be used by Tiki
			if (!isset(parent::$s['ajax']['custom']) || parent::$s['ajax']['custom'] !== false) {
				$ca = array(
					'var vars = {}, hashes, hash, size, sort, sorts, filter, filtered, colfilters, extfilters,
						params = [], dir, newurl, p = table.config.pager;',
					//parse out url parameters
					'hashes = url.slice(url.indexOf(\'?\') + 1).split(\'&\');',
					'for(var i = 0; i < hashes.length; i++) {',
					'	hash = hashes[i].split(\'=\');',
					'	vars[hash[0]] = hash[1];',
					'}',
					//map of columns keys to sort and filter server side parameters
					'sort = ' . json_encode(parent::$s['ajax']['sort']) . ';',
					'colfilters = ' . json_encode(parent::$s['ajax']['colfilters']) . ';',
					'extfilters = ' . json_encode(parent::$s['ajax']['extfilters']) . ';',
					//iterate through url parameters
					'$.each(vars, function(key, value) {',
						//handle sort parameters
					'	if (sort && key in sort) {',
					'		value == 0 ? dir = \'_asc\' : dir = \'_desc\';',
							//add sort if not yet defined or add sort for multiple comma-separated sort parameters
					'		typeof sorts === \'undefined\' ? sorts = sort[key] + dir : sorts += \',\' + sort[key] + dir;',
					'	}',
						//handle column and external filter parameters
					'	if ($.inArray(value, extfilters) > -1) {',
					'		filter = true;',
					'		params.push(decodeURIComponent(value));',
					'	} else if (key in colfilters) {',
					'		filter = true;',
					'		colfilters[key][value] ? params.push(colfilters[key][value]) : params.push(colfilters[key]
								+ \'=\' + value);',
					'	}',
					'});',
					//convert to tiki sort param sort_mode
					'if (sorts) {',
					'	params.push(\'sort_mode=\' + sorts);',
					'}',
					//offset parameter
					'size = parseInt(p.$size.val());',
					'filtered = typeof p.ajaxData === \'undefined\' ? 0 : p.ajaxData.filtered;',
					'offset = filter || ((p.page * size) >= filtered) ? \'\' : offset = \'&'
						. parent::$s['ajax']['offset'] . '=\' + (p.page * size); ',
					//build url, starting with no parameters
					'newurl = url.slice(0,url.indexOf(\'?\'));',
					'newurl = newurl + \'?numrows=\' + size + offset + \'&tsAjax=y\';',
					'$.each(params, function(key, value) {',
					'	newurl = newurl + \'&\' + value;',
					'});',
					'return newurl;'
				);
			} else {
				$ca = array(
					'var p = table.config.pager, size = parseInt(p.$size.val()), filter, filtered, offset, total;',
					'if (typeof p.ajaxData === \'undefined\') {',
					'	filtered = 0;',
					'	filter = false;',
					'} else {',
					'	filtered = typeof p.ajaxData.filtered === \'undefined\' ? 0 : p.ajaxData.filtered;',
					'	filter = typeof p.ajaxData.filter === \'undefined\' ? false : true;',
					'	total = typeof p.ajaxData.total === \'undefined\' ? 0 : p.ajaxData.total;',
					'	filter = filter === false || filtered == total ? false : true;',
					'}',
					'offset = (filter === true || ((p.page * size) >= filtered)) ? \'\' : \''
						. parent::$s['ajax']['offset'] . '\' + \'=\' + (p.page * size);',
					'return url + \'&tsAjax=y&\' + offset + \'&numrows=\' + size;'
				);
			}
			if (count($ca) > 0) {
				$p[] = $this->iterate(
					$ca,
					$pre . 'customAjaxUrl: function(table, url) {',
					$this->nt3 . '}',
					$this->nt4,
					'',
					''
				);
			}
			if (parent::$pager) {
				//pager css
				$pc[] = 'container: \'ts-pager\'';
				$p[] = $this->iterate($pc, $pre . 'css: {', $this->nt3 . '}', $this->nt4, '');
				//pager selectors
				$ps[] = 'container : \'div#' . parent::$s['pager']['controls']['id'] . '\'';
				$p[] = $this->iterate($ps, $pre . 'selectors: {', $this->nt3 . '}', $this->nt4, '');
			}
		}
		if (count($p) > 0) {
			if (!parent::$ajax) {
				$code = $this->iterate($p, '.tablesorterPager({', $this->nt . '});', $this->nt2, '');
				parent::$code[self::$level1] = $code;
			} else {
				$wo = array_merge(parent::$tempcode['wo'], $p);
				parent::$code['main']['widgetOptions'] = $this->iterate($wo, $this->nt2
					. 'widgetOptions : {', $this->nt2 . '}', $this->nt3, '');
				unset(parent::$tempcode['wo']);
			}
		}
	}
}
