<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}
define('WIKI_XML', 'wiki.xml');
define('PAGE_TXT', 'page.txt');

class XmlLib extends TikiLib {
	var $errors = array();
	var $errorsArgs = array();
	var $xml = '';
	var $zip = '';
	var $config = array('comments'=>true, 'attachments'=>true, 'history'=>true, 'images'=>true, 'debug'=>false);
	var $structureStack = array();
	function XmlLib() {
		global $dbTiki;
		parent::TikiLib($dbTiki);
	}
	function get_error() {
		$str = '';
		foreach ($this->errors as $i=>$error) {
			$str = $error;
			if (is_array($this->errorsArgs[$i])) {
				$str .= ': '.implode(', ', $this->errorsArgs[$i]);
			} else {
				$str .= $this->errorsArgs[$i];
			}
		}
		return $str;
	}

	/* Export a list of pages or a structure */
	function export_pages($pages=null, $structure=null, $zipFile='dump/xml.zip', $config=null) {
		if (!($this->zip = new ZipArchive())) {
			$this->errors[] = 'Problem zip initialisation';
			$this->errorsArgs[] = '';
			return false;
		}
		if (!$this->zip->open($zipFile, ZIPARCHIVE::OVERWRITE)) {
			$this->errors[] = 'Can not open the file';
			$this->errorsArgs[] = $zipFile;
			return false;
		}

		if (!empty($config)) {
			$this->config = array_merge($this->config, $config);
		}
		$this->xml .= '<?xml version="1.0" encoding="UTF-8"?>';
		if (count($pages) >= 1) {
			$this->xml .= '<pages>';
			foreach ($pages as $page) {
				if (!$this->export_page($page)) {
					return false;
				}
			}
			$this->xml .= '</pages>';
		}
		if (!empty($structure)) {
			global $structlib; include_once('lib/structures/structlib.php');
			$pages = $structlib->s_get_structure_pages($structure);
			$stack = array();
			foreach ($pages as $page) {
				while (count($stack) && $stack[count($stack) - 1] != $page['parent_id']) {
					array_pop($stack);
					$this->xml .= '</structure>';
				}
				$this->xml .= '<structure>';
				$stack[] = $page['page_ref_id'];
				if (!$this->export_page($page['pageName'])) {
					return false;
				}
			}
			while (count($stack)) {
				array_pop($stack);
				$this->xml .= '</structure>';
			}
		}

		if (!$this->zip->addFromString(WIKI_XML, $this->xml) ) {
			$this->errors[] = 'Can not add the xml';
			$this->errorsArgs[] = WIKI_XML;
			return false;
		}
		if ($this->config['debug']) {
			echo '<pre>'.htmlspecialchars($this->xml).'</pre>';
		}
		$this->zip->close();
		return true;
	}

	/* export one page */
	function export_page($page) {
		global $tikilib, $prefs, $smarty;
		
		$info = $tikilib->get_page_info($page);
		if (empty($info)) {
			$this->errors[] = 'Page does not exist';
			$this->errorsArgs[] = $page;
			return false;
		}
		$dir = $page;
		$info['zip'] = "$dir/".PAGE_TXT;
		$smarty->assign_by_ref('info', $info);

		if (!$this->zip->addFromString($info['zip'], $info['data'])) {
			$this->errors[] = 'Can not add the page';
			$this->errorsArgs[] = $info['zip'];
			return false;
		}
		if ($prefs['feature_wiki_comments'] == 'y' && $this->config['comments']) {
			global $dbTiki; include_once('lib/commentslib.php'); $commentslib = new Comments($dbTiki);
			$comments = $commentslib->get_comments('wiki page:'.$page, 0, 0, 0, 'commentDate_asc', '', 0, 'commentStyle_plain');
			if (!empty($comments['cant'])) {
				$smarty->assign_by_ref('comments', $comments['data']);
			}
		}
		if ($prefs['feature_wiki_pictures'] == 'y' && $this->config['images'] && preg_match_all('/\{img[^\}]+src=["\']?([^\'" }]+)["\']*/', $info['data'], $matches)) {
			global $tikiroot;
			$images = array();
			foreach ($matches[1] as $match) {
				if (preg_match('|img/wiki_up/(.*)|', $match, $m)) {
					$image = array('filename' => $m[1], 'where' => 'wiki', 'zip'=>"$dir/images/wiki/".$m[1], 'wiki'=>$match);
					if (!$this->zip->addFile($match, $image['zip'])) {
						$this->errors[] = 'Can not add the image';
						$this->errorsArgs[] = $m[1];
						return false;
					}
				} elseif (preg_match('|show_image.php\?(.*)|', $match, $m)) {
					global $imagegallib; include_once('lib/imagegals/imagegallib.php');
					$img = $this->httprequest($_SERVER['HTTP_HOST'].$tikiroot.$match);
					$this->parse_str($m[1], $p);
					if (isset($p['name']) && isset($p['galleryId']))
						$id = $imagegallib->get_imageid_byname($p['name'], $p['galleryId']);
					elseif (isset($p['name']))
						$id = $imagegallib->get_imageid_byname($p['name']);
					elseif (isset($p['id']))
						$id = $p['id'];
					$image = array('where' => 'gal', 'zip' => "$dir/images/gal/".$id, 'wiki'=>$match);
					if (!$this->zip->addFromString($image['zip'], $img)) {
						$this->errors[] = 'Can not add the image';
						$this->errorsArgs[] = $m[1];
						return false;
						}
				} elseif (preg_match('|tiki-download_file.php\?(.*)|', $match, $m)) {
					$img = $this->httprequest($_SERVER['HTTP_HOST'].$tikiroot.$match);
					$this->parse_str($m[1], $p);
					$image = array('where' => 'fgal', 'zip'=>"$dir/images/fgal/".$p['fileId'], 'wiki'=>$match);
					if (!$this->zip->addFromString($image['zip'], $img)) {
						$this->errors[] = 'Can not add the image';
						$this->errorsArgs[] = $m[1];
						return false;
					}
				} /* else no idea where the img comes from - suppose there are outside tw */
				$images[] = $image;
			}
			$smarty->assign_by_ref('images', $images);
		}
		if ($prefs['feature_wiki_attachments'] == 'y' && $this->config['attachments']) {
			global $wikilib; include_once('lib/wiki/wikilib.php');
			$attachments = $wikilib->list_wiki_attachments($page, 0, -1);
			if (!empty($attachments['cant'])) {
				foreach ($attachments['data'] as $key=>$att) {
					$att_info = $wikilib->get_item_attachment($att['attId']);
					$attachments['data'][$key]['zip'] = "$dir/attachments/".$att['attId'];
					if ($prefs['w_use_dir']) {
						if (!$this->zip->addFile($prefs['w_use_dir'].$att_info['path'], $attachments['data'][$key]['zip'])) {
							$this->errors[] = 'Can not add the attachment';
							$this->errorsArgs[] = $att_info['attId'];
							return false;
						}
					} else {
						if (!$this->zip->addFromString($attachments['data'][$key]['zip'], $att_info['data'])) {
							$this->errors[] = 'Can not add the attachment';
							$this->errorsArgs[] = $att_info['attId'];
							return false;
						}
					}
				}
				$smarty->assign_by_ref('attachments', $attachments['data']);
			}
		}
		if ($prefs['feature_history'] == 'y' && $this->config['history']) {
			global $histlib; include_once ('lib/wiki/histlib.php');
			$history = $histlib->get_page_history($page,false);
			foreach ($history as $key=>$hist) {
				$all = $histlib->get_version($page, $hist['version']); // can be optimised if returned in the list
				//$history[$key]['data'] = $all['data'];
				$history[$key]['zip'] = "$dir/history/".$all['version'].'.txt';
				if (!$this->zip->addFromString($history[$key]['zip'], $all['data'])) {
					$this->errors[] = 'Can not add the history';
					$this->errorsArgs[] = $all['version'];
					return false;
				}
			}
			$smarty->assign_by_ref('history', $history);
		}

		$smarty->assign_by_ref('config', $this->config);
		$this->xml .=  $smarty->fetch('tiki-export_page_xml.tpl');
		return true;
	}
	/* import pages or structure */
	function import_pages($zipFile='dump/xml.zip', $config=null) {
		global $tikilib, $wikilib, $prefs, $tiki_p_wiki_attach_files, $user, $tiki_p_edit_comments,$dbTiki, $tikidomain;
		if (!empty($config)) {
			$this->config = array_merge($this->config, $config);
		}
		if (!($this->zip = new ZipArchive())) {
			$this->errors[] = 'Problem zip initialisation';
			$this->errorsArgs[] = '';
			return false;
		}
		if (!$this->zip->open($zipFile)) {
			$this->errors[] = 'Can not open the file';
			$this->errorsArgs[] = $zipFile;
			return false;
		}

		if (!($this->xml = $this->zip->getFromName(WIKI_XML))) {
			$this->errors[] = 'Can not unzip';
			$this->errorsArgs[] = WIKI_XML;
			return false;
		}

		$parser = &new page_Parser();
		$parser->setInput($this->xml);
		$ok = $parser->parse();
		if (PEAR::isError($ok)) {
			$this->errors[] = $ok->getMessage();
			$this->errorsArgs[] = '';
			return false;
		}
		$infos = $parser->getPages();

		if ($this->config['debug']) {echo 'XML PARSING<pre>';print_r($info);echo '</pre>';}

		foreach ($infos as $info) {
			if (!$this->create_page($info)) {
				return false;
			}
		}
		$this->zip->close();
		return true;
	}
	/* create a page from an xml parsing result */
	function create_page($info) {
		global $tikilib, $wikilib, $prefs, $tiki_p_wiki_attach_files, $user, $tiki_p_edit_comments,$dbTiki, $tikidomain;

		$dir = $info['name'];
		if (!($info['data'] = $this->zip->getFromName($info['zip']))) {
			$this->errors[] = 'Can not unzip';
			$this->errorsArgs[] = $info['zip'];
			return false;			
		}

		$tikilib->create_page($info['name'], 0, $info['data'], $this->now, $info['comment'], $config['fromUser']? $config['fromUser']: $info['user'], $config['fromSite']?$config['fromSite']: $info['ip'], $info['description']);

		if ($prefs['feature_wiki_comments'] == 'y' && $tiki_p_edit_comments == 'y') {
			foreach ($info['comments'] as $comment) {
				global $commentslib; include_once('lib/commentslib.php'); $commentslib = new Comments($dbTiki);
				$parentId = empty($comment['parentId']) ? 0: $newThreadIds[$comment['parentId']];
				if ($parentId) {
					$reply_info = $commentslib->get_comment($parentd);
					$in_reply_to = $reply_info['message_id'];
				}
				$newThreadIds[$comment['threadId']] = $commentslib->post_new_comment('wiki page:'.$info['name'], $parentId, $config['fromUser']? $config['fromUser']: $comment['user'], $comment['title'], $comment['data'], $message_id, $reply_to);
			}
		}
		if ($prefs['feature_wiki_attachments'] == 'y' && $tiki_p_wiki_attach_files == 'y') {
			foreach ($info['attachments'] as $attachment) {
				if (!($attachment['data'] = $this->zip->getFromName($attachment['zip']))) {
					$this->errors[] = 'Can not unzip attachement';
					$this->errorsArgs[] = $attachment['zip'];
					return false;	
				}
				if ($prefs['w_use_db'] == 'y') {
					$fhash = '';
				} else {
					$fhash = $this->get_attach_hash_file_name($attachment['filename']);
					if ($fw = fopen($prefs['w_use_dir'].$fhash, 'wb')) {
						if (!fwrite($fw, $attachment['data'])) {
							$this->errors[] = 'Cannot write to this file';
							$this->errorsArgs[] = $prefs['w_use_dir'].$fhash;
						}
						fclose($fw);
						$attachment['data'] = '';
					} else {
						$this->errors[] = 'Cannot open this file';
						$this->errorsArgs[] = $prefs['w_use_dir'].$fhash;
					}
				}
				global $wikilib; include_once('lib/wiki/wikilib.php');
				$wikilib->wiki_attach_file($info['name'], $attachment['filename'], $attachment['filetype'], $attachment['filesize'], $attachment['data'], $attachment['comment'], $attachment['user'], $fhash);
				//change the page data attach is needed $res['attId']
				//$res = $wikilib->get_wiki_attach_file($info['name'], $attachment['filename'], $attachment['type'], $attachment['size']);
			}
		}

		if ($prefs['feature_wiki_pictures'] == 'y') {
			foreach ($info['images'] as $image) {
				if (!($image['data'] = $this->zip->getFromName($image['zip']))) {
					$this->errors[] = 'Can not unzip image';
					$this->errorsArgs[] = $image['zip'];
					return false;
				}
				if ($image['where'] == 'wiki') {
					$wiki_up = 'img/wiki_up/';
					if ($tikidomain)
						$wiki_up.= "$tikidomain/";
					$name = str_replace('img/wiki_up/', '', $image['wiki']);
					file_put_contents( $wiki_up.$name, $image['data']);
				}
			}
		}

		if ($prefs['feature_history'] == 'y') {
			foreach ($info['history'] as $version) {
				if (!($version['data'] = $this->zip->getFromName($version['zip']))) {
					$this->errors[] = 'Can not unzip history';
					$this->errorsArgs[] = $version['version'];
					return false;	
				}			
				$query = 'insert into `tiki_history`(`pageName`, `version`, `lastModif`, `user`, `ip`, `comment`, `data`, `description`) values(?,?,?,?,?,?,?,?)';
				$this->query($query,array($info['name'], $version['version'], $tikilib->now, $version['user'], $version['ip'], $version['comment'], $version['data'], $version['description']));
			}
		}
		if ($prefs['feature_wiki_structure'] == 'y' && !empty($info['structure'])) {
			global $structlib; include_once('lib/structures/structlib.php');
			if ($info['structure'] == 1) {
				$this->structureStack[$info['structure']] = $structlib->s_create_page(null, null , $info['name'], $info['alias']);
			} else {
				$structlib->s_create_page($this->structureStack[$info['structure'] - 1], $after, $info['name'], '');
			}
		}
		return true;
	}

}
global $dbTiki;
$xmllib = new XmlLib($dbTiki);

require_once('lib/pear/XML_Parser/Parser.php');
class page_Parser extends XML_Parser {
	var $page;
	var $currentTag = null;
	var $context = null;
	var $folding = false; // keep tag as original
	var $commentsStack = array();
	var $commentId = 0;
	var $iStructure = 0;
	function startHandler($parser, $name, $attribs) {
		switch ($name) {
		case 'page':
			$this->context = null;
			if (is_array($attribs)) {
				$this->page = array('data'=>'', 'comment'=>'', 'description'=>'', 'user'=>'admin', 'ip'=>'0.0.0.0', 'lang'=>'', 'is_html'=>false, 'hash'=>null, 'wysiwyg'=>null);
				$this->page = array_merge($this->page, $attribs);
			}
			if ($this->iStructure > 0 ) {
				$this->page['structure'] = $this->iStructure;
			}
			break;
		case 'structure':
			++$this->iStructure;
			break;
		case 'comments':
			$comentsStack = array();
		case 'attachments':
		case 'history':
		case 'images':
			$this->context = $name;
			$this->i = -1;
			break;
		case 'comment':
			if ($this->context == 'comments') {
				++$this->i;
				$this->page[$this->context][$this->i] = $attribs;
				$this->page[$this->context][$this->i]['parentId'] = empty($this->commentsStack)?0: $this->commentsStack[count($this->commentsStack) - 1];
				$this->page[$this->context][$this->i]['threadId'] = ++$this->commentId;
				array_push($this->commentsStack, $this->commentId);
			} else {
				$this->currentTag = $name;
			}
			break;
		case 'attachment':
			++$this->i;
			$this->page[$this->context][$this->i] = array('comment'=>'');
			$this->page[$this->context][$this->i] = array_merge($this->page[$this->context][$this->i], $attribs);
			break;
		case 'version':
			++$this->i;
			$this->page[$this->context][$this->i] =  array('comment' =>'', 'description'=>'', 'ip'=>'0.0.0.0');
			$this->page[$this->context][$this->i] =  array_merge($this->page[$this->context][$this->i], $attribs);
			break;
		case 'image':
			++$this->i;
			$this->page[$this->context][$this->i] = $attribs;

			break;
		default:
			$this->currentTag = $name;
			break;
		}
	}
	function endHandler($parser, $name) {
		$this->currentTag = null;
		switch ($name) {
		case 'comments':
		case 'attachements':
		case 'history':
		case 'images':
			$this->context = null;
			break;
		case 'comment':
			array_pop($this->commentsStack);
			break;
		case 'page':
			$this->pages[] = $this->page;
			break;
		case 'structure':
			--$this->iStructure;
			break;
		}
	}
	function cdataHandler($parser, $data) {
		$data = trim($data);
		if (empty($data)) {
			return true;
		}
		if (empty($this->context)) {
			$this->page[$this->currentTag] = $data;
		} else {
			$this->page[$this->context][$this->i][$this->currentTag] = $data;
		}
	}
	function getPages() {
		return $this->pages;
	}
}
?>