<?php
require_once __DIR__ . "/LegalQuery.php";

/**
 * eval source code in PHP or JSON format
 *
 */
class VarEval {
	/**
	 * Source to run
	 *
	 * @var string
	 */
	private $_source;

	/**
	 * Source Format
	 *
	 * @var string
	 */
	private $_format;

	/**
	 * current MongoDB
	 *
	 * @var MongoDB
	 */
	private $_db;

	function __construct($source, $format = "array", MongoDB\Database $db = null) {
		$this->_source = $source;

		$this->_format = $format;
		if (!$this->_format) {
			$this->_format = "array";
		}

		$this->_db = $db;
	}

	/**
	 * execute the code
	 *
	 * @return mixed
	 */
	function execute() {
		if ($this->_format == "array") {
			return $this->_runPHP();
		}
		else if ($this->_format == "json") {
			return $this->_runJson();
		}
	}

	private function _runPHP() {
		$this->_source = "return " . $this->_source . ";";
		if (function_exists("token_get_all")) {//tokenizer extension may be disabled
			$php = "<?php\n" . $this->_source . "\n?>";
			$tokens = token_get_all($php);
			foreach ($tokens as $token) {
				$type = $token[0];
				if (is_long($type)) {
					if (in_array($type, array(
							T_OPEN_TAG,
							T_RETURN,
							T_WHITESPACE,
							T_ARRAY,
							T_LNUMBER,
							T_DNUMBER,
							T_CONSTANT_ENCAPSED_STRING,
							T_DOUBLE_ARROW,
							T_CLOSE_TAG,
							T_NEW,
							T_DOUBLE_COLON
							))) {
						continue;
					}

					if ($type == T_STRING) {
						$func = strtolower($token[1]);
						if (in_array($func, array(
								//keywords allowed
								"mongoid",
								"mongocode",
								"mongodate",
								"mongoregex",
								"mongobindata",
								"mongoint32",
								"mongoint64",
								"mongodbref",
								"mongominkey",
								"mongomaxkey",
								"mongotimestamp",
								"true",
								"false",
								"null",
								"__set_state",
								"stdclass"
							))) {
							continue;
						}
					}
					exit("For your security, we stoped data parsing at '(" . token_name($type) . ") " . $token[1] . "'.");
				}
			}
		}
		return eval($this->_source);
	}

	private function _runJson() {
		//var_dump($this->_source);
		$this->_source = formatJsonQuery($this->_source);
		//var_dump($this->_source);
		//var_dump(json_decode($this->_source, true));
		return json_decode($this->_source, true);
	}

	private function _fixEmptyObject(&$object) {
		if (is_array($object)) {
			foreach ($object as &$v) {
				$this->_fixEmptyObject($v);
			}
		}
		else if (is_string($object) && $object === "__EMPTYOBJECT__") {
			$object = new stdClass();
		}
	}
}

?>
