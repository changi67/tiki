<?php
include_once('lib/usermodules/usermoduleslib.php');

class ModLib extends TikiLib {
	function ModLib($db) {
		# this is probably uneeded now
		if (!$db) {
			die ("Invalid db object passed to ModLib constructor");
		}

		$this->db = $db;
	}

	function replace_user_module($name, $title, $data) {

		if ((!empty($name)) && (!empty($title)) && (!empty($data))) {
			$query = "delete from `tiki_user_modules` where `name`=?";
			$result = $this->query($query,array($name),-1,-1,false);
			$query = "insert into `tiki_user_modules`(`name`,`title`,`data`) values(?,?,?)";

			$result = $this->query($query,array($name,$title,$data));
			return true;
		}
	}

	function assign_module($name, $title, $position, $order, $cache_time = 0, $rows = 10, $groups, $params,$type) {

		$query = "delete from `tiki_modules` where `name`=?";
		$result = $this->query($query,array($name));
		//check for valid values
		$cache_time = is_numeric($cache_time) ? $cache_time : 0;
		$rows = is_numeric($rows) ? $rows : 10;
		$query = "insert into `tiki_modules`(`name`,`title`,`position`,`ord`,`cache_time`,`rows`,`groups`,`params`,`type`) values(?,?,?,?,?,?,?,?,?)";
		$result = $this->query($query,array($name,$title,$position,(int) $order,(int) $cache_time,(int) $rows,$groups,$params,$type));
		if ($type == "D" || $type == "P") {
			global $usermoduleslib;
			$usermoduleslib->add_module_users($name,$title,$position,$order,$cache_time,$rows,$groups,$params,$type);
		}
		return true;
	}

	function get_assigned_module($name) {
		$query = "select * from `tiki_modules` where `name`=?";

		$result = $this->query($query,array($name));
		$res = $result->fetchRow();

		if ($res["groups"]) {
			$grps = unserialize($res["groups"]);

			$res["module_groups"] = '';

			foreach ($grps as $grp) {
				$res["module_groups"] .= " $grp ";
			}
		}

		return $res;
	}

	function unassign_module($name) {
		$query = "delete from `tiki_modules` where `name`=?";

		$result = $this->query($query,array($name));
		$query = "delete from `tiki_user_assigned_modules` where `name`=?";
		$result = $this->query($query,array($name));
		return true;
	}

	function get_rows($name) {
		$query = "select `rows` from `tiki_modules` where `name`=?";

		$rows = $this->getOne($query,array($name));

		if ($rows == 0)
			$rows = 10;

		return $rows;
	}

	function module_up($name) {
		$query = "update `tiki_modules` set `ord`=`ord`-1 where `name`=?";

		$result = $this->query($query,array($name));
		return true;
	}

	function module_down($name) {
		$query = "update `tiki_modules` set `ord`=`ord`+1 where `name`=?";

		$result = $this->query($query,array($name));
		return true;
	}

	function get_all_modules() {
		$user_modules = $this->list_user_modules();

		$all_modules = array();

		foreach ($user_modules["data"] as $um) {
			$all_modules[] = $um["name"];
		}

		// Now add all the system modules
		$h = opendir("templates/modules");

		while (($file = readdir($h)) !== false) {
			if (substr($file, 0, 3) == 'mod') {
				if (!strstr($file, "nocache")) {
					$name = substr($file, 4, strlen($file) - 8);

					$all_modules[] = $name;
				}
			}
		}

		closedir ($h);
		return $all_modules;
	}

	function remove_user_module($name) {

		$this->unassign_module($name);
		$query = " delete from `tiki_user_modules` where `name`=?";
		$result = $this->query($query,array($name));
		return true;
	}

	function list_user_modules() {
		$query = "select * from `tiki_user_modules`";

		$result = $this->query($query,array());
		$query_cant = "select count(*) from `tiki_user_modules`";
		$cant = $this->getOne($query_cant,array());
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function clear_cache() {
		global $tikidomain;
		$h = opendir("modules/cache/$tikidomain");
		while (($file = readdir($h)) !== false) {
			if (substr($file, 0, 3) == 'mod') {
				$file = "modules/cache/$tikidomain" . $file;
				unlink ($file);
			}
		}
		closedir($h);
	}

}

$modlib = new ModLib($dbTiki);

?>
