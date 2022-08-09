<?php

import("classes.BaseController");

class ServerController extends BaseController {

	/** server infomation **/
	public function doIndex() {
		$db = $this->_mongo->selectDB("admin");

		//command line
		try {
			$query = $db->command(array("getCmdLineOpts" => 1))->toArray()[0];
			if (isset($query["argv"])) {
				$this->commandLine = implode(" ", $query["argv"]);
			} else {
				$this->commandLine = "";
			}
		} catch (Exception $e) {
			$this->commandLine = "";
		}

		//web server
		$this->webServers = array();
		if (isset($_SERVER["SERVER_SOFTWARE"])) {
			list($webServer) = explode(" ", $_SERVER["SERVER_SOFTWARE"]);
			$this->webServers["Web server"] = $webServer;
		}
		$this->webServers["<a href=\"http://www.php.net\" target=\"_blank\">PHP version</a>"] = "PHP " . PHP_VERSION;
		$this->webServers["<a href=\"http://www.php.net/mongodb\" target=\"_blank\">PHP extension</a>"] = "<a href=\"http://pecl.php.net/package/mongodb\" target=\"_blank\">mongodb</a>/" . RMongo::getVersion();
		$this->webServers["<a href=\"https://www.mongodb.com/docs/php-library/current\" target=\"_blank\">PHP Library</a>"] = "<a href=\"https://www.mongodb.com/docs/php-library/current/\" target=\"_blank\">library</a>/" . RMongo::getLibraryVersion();
		$this->directives = ini_get_all("mongodb");

		//startupLogs
		$this->startupLogs  = array();
		try {
			$query = $this->_mongo->selectDB("local")->selectCollection("startup_log")->find();
			foreach ($query as $one) {
				foreach ($one as $param=>$value) {
					if ($param == "syncedTo" || $param == "localLogTs") {
						if ($value->inc > 0) {
							$one[$param] = date("Y-m-d H:i:s", $value->sec) . "." . $value->inc;
						}
					} elseif($param == "buildinfo") {
						$one[$param] = ['version'=>$value['version']];
					}
				}
				$this->startupLogs[] = $one;
			}
		} catch (Exception $e) {
			throw $e;
		}

		//build info
		$this->buildInfos = array();
		try {
			$ret = $db->command(array("buildinfo" => 1))->toArray()[0];
			if ($ret["ok"]) {
				unset($ret["ok"]);
				$this->buildInfos = $ret;
			}
		} catch (Exception $e) {

		}

		//connection
		$this->connections = array(
			"Host" => $this->_server->mongoHost(),
			"Port" => $this->_server->mongoPort(),
			"Username" => "******",
			"Password" => "******"
		);

		$this->display();
	}

	/** Server Status **/
	public function doStatus() {
		$this->status = array();

		try {
			//status
			$db = $this->_mongo->selectDB("admin");
			$ret = $db->command(["serverStatus" => 1])->toArray()[0];
			if ($ret["ok"]) {
				unset($ret["ok"]);
				$this->status = $ret;
				foreach ($this->status as $index => $_status) {
					$json = $this->_highlight($_status, "json");
					if ($index == "uptime") {//we convert it to days
						if ($_status >= 86400) {
							$json .= "s (" . ceil($_status/86400) . "days)";
						}
					}
					$this->status[$index] =  $json;
				}
			}
		} catch (Exception $e) {

		}

		$this->display();
	}

	/** show databases **/
	public function doDatabases() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"];
		foreach ($this->dbs as $index => $db) {
			$mongodb = $this->_mongo->selectDB($db["name"]);
			$ret = $mongodb->command(["dbstats" => 1])->toArray()[0];
			$ret["collections"] = count(MDb::listCollections($mongodb));
			if (isset($db["sizeOnDisk"])) {
				$ret["diskSize"] = r_human_bytes($db["sizeOnDisk"]);
				$ret["dataSize"] = r_human_bytes($ret["dataSize"]);
			} else {
				$ret["diskSize"] = "-";
				$ret["dataSize"] = "-";
			}
			$ret["storageSize"] = r_human_bytes($ret["storageSize"]);
			$ret["indexSize"] = r_human_bytes($ret["indexSize"]);
			$this->dbs[$index] = array_merge($this->dbs[$index], $ret);

		}
		$this->dbs = rock_array_sort($this->dbs, "name");
		$this->display();
	}

	/** execute command **/
	public function doCommand() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"];

		if (!$this->isPost()) {
			x("command", json_format("{listCommands:1}"));
			if (!x("db")) {
				x("db", "admin");
			}
		}

		if ($this->isPost()) {
			$command = xn("command");
			$format = x("format");
			if ($format == "json") {
				$command = 	$this->_decodeJson($command);
			} else {
				$eval = new VarEval($command);
				$command = $eval->execute();
			}
			if (!is_array($command)) {
				$this->message = "You should send a valid command";
				$this->display();
				return;
			}
			$this->ret = $this->_highlight($this->_mongo->selectDB(xn("db"))->command($command)->toArray()[0], $format);
		}
		$this->display();
	}

	/** processlist **/
	public function doProcesslist() {
		$this->progs = array();

		try {
			$query = $this->_mongo->selectDB("admin")->command(['currentOp'=>1])->toArray()[0];

			if ($query["ok"]) {
				$this->progs = $query["inprog"];
			}
			foreach ($this->progs as $index => $prog) {
				foreach ($prog as $key=>$value) {
					if (is_array($value)) {
						$this->progs[$index][$key] = $this->_highlight($value, "json");
					}
				}
			}
		} catch (Exception $e) {

		}
		$this->display();
	}

	/** kill one operation in processlist **/
	public function doKillOp() {
		$opid = xi("opid");
		try{
			$query = $this->_mongo->selectDB("admin")->command(['killOp'=>1, 'op'=>$opid])->toArray()[0];
			$this->redirect("server.processlist");
		} catch(Exception $e) {
			throw $e;
		}
		$this->ret = $this->_highlight($query, "json");
		$this->display();
	}

	/** create databse **/
	public function doCreateDatabase() {
		if ($this->isPost()) {
			$name = trim(xn("name"));
			if (empty($name)) {
				$this->error = "Please input a valid database name.";
				$this->display();
				return;
			}
			$this->message = "New database created.";
			$this->_mongo->selectDb($name)->createCollection('T');
		}
		$this->display();
	}

	/** replication status **/
	public function doReplication() {
		$this->status = array();

		try {
			//用replSetGetStatus替换db.getReplicationInfo();
			//$ret = $this->_mongo->selectDB("local")->execute('function () { return db.getReplicationInfo(); }');
			$ret = $this->_mongo->selectDB("admin")->command(['replSetGetStatus'=>1])->toArray();
			$status = $ret[0];
			//echo json_encode($status, JSON_PRETTY_PRINT);
			foreach ($status as $param => $value) {
				if ($param == "date") {
					$this->status["date"] = $value->toDateTime()->format('Y-m-d H:i:s');
				} else if($param == "members") {
					continue;
				} else {
					$this->status[$param] = $value;
				}
			}

			//members
			$this->members = array();
			foreach ($status['members'] as $one) {
				foreach ($one as $param=>$value) {
					if ($param == "optimeDate" || $param == "lastAppliedWallTime" || $param == "lastDurableWallTime" || 
						$param == "electionDate" || $param == "optimeDurableDate" || $param == "lastHeartbeat" ||
						$param == "lastHeartbeatRecv") {
						$one[$param] = $value->toDateTime()->format('Y-m-d H:i:s');
					} elseif ($param == "uptime") {
						$one[$param] = $value . '(' . r_human_duration($value) . ')';
					}
				}
				$this->members[] = $one;
			}
		} catch (Exception $e) {
			throw $e;
		}

		//me
		try {
			$this->me = $this->_mongo->selectDB("local")->selectCollection("me")->findOne();
		} catch (Exception $e) {
			$this->me = array();
			throw $e;
		}

		$this->display();
	}
}

?>
