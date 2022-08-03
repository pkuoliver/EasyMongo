<?php

define("MONGO_EXPORT_PHP", "array");
define("MONGO_EXPORT_JSON", "json");

class VarExportor {
	private $_db;
	private $_var;
	private $_phpParams = array();
	private $_jsonParams = array();
	private $_paramIndex = 0;

	/**
	 * construct exportor
	 *
	 * @param MongoDB\Database $db current db you are operating
	 * @param mixed $var variable
	 */
	function __construct(MongoDB\Database $db, $var) {
		$this->_db = $db;
		$this->_var = $var;
	}

	/**
	 * Export the variable to a string
	 *
	 * @param string $type variable type (array or json)
	 * @param boolean $fieldLabel if add label to fields
	 * @return string
	 */
	function export($type = MONGO_EXPORT_PHP, $fieldLabel = false) {
		if ($fieldLabel) {
			$this->_var = $this->_addLabelToArray($this->_var);
		}
		if ($type == MONGO_EXPORT_PHP) {
			return $this->_exportPHP();
		}
		return $this->_exportJSON();
	}

	/**
	 * Export the variable to PHP
	 *
	 * @return string
	 */
	private function _exportPHP() {
		$var = $this->_formatVar($this->_var);
		$string = var_export($var, true);
		$params = array();
		foreach ($this->_phpParams as $index => $value) {
			$params["'" . $this->_param($index) . "'"] = $value;
		}

		return strtr($string, $params);
	}

	/**
	 * Substr utf-8 version
	 *
	 * @param string $str
	 * @param int $from
	 * @param int $len
	 * @return unknown
	 * @author sajjad at sajjad dot biz (copied from PHP manual)
	 */
	private function _utf8_substr($str, $from, $len) {
		return function_exists('mb_substr') ?
			mb_substr($str, $from, $len, 'UTF-8') :
			preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $from .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $len .'}).*#s','$1', $str);
	}

	private function _addLabelToArray($array, $prev = "") {
		$ret = array();
		$cutLength = 150;
		foreach ($array as $key => $value) {
			if (is_string($key)) {
				$newKey = $prev . ($prev === ""?"":".") . "rockfield." . $key;
				if (is_string($value) && strlen($value) > $cutLength) {
					$value = $this->_utf8_substr($value, 0, $cutLength);
					$value = $value . " __rockmore.{$newKey}.rockmore__";
				}
				$ret[$newKey . ".rockfield"] = $value;
				if (is_array($value)) {
					$ret[$newKey . ".rockfield"] = $this->_addLabelToArray($value, $newKey);
				}
			}
			else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	private function _exportJSON() {
		$var = $this->_formatVarAsJSON($this->_var);
		$string = json_encode($var, JSON_UNESCAPED_UNICODE);
		
		//Remove "\/" escape
		$string = str_replace('\/', "/", $string);

		$params = array();
		foreach ($this->_jsonParams as $index => $value) {
			$params['"' . $this->_param($index) . '"'] = $value;
		}
		return json_unicode_to_utf8(json_format(strtr($string, $params)));
	}

	private function _param($index) {
		return "%{MONGO_PARAM_{$index}}";
	}

	private function _formatVar($var) {
		if (is_scalar($var) || is_null($var)) {
			switch (gettype($var)) {
				default:
					return $var;
			}
		}
		if (is_array($var)) {
			foreach ($var as $index => $value) {
				$var[$index] = $this->_formatVar($value);
			}
			return $var;
		}
		if (is_object($var)) {
			$this->_paramIndex ++;
			switch (get_class($var)) {
				case "stdClass":
					$this->_phpParams[$this->_paramIndex] = array();
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\ObjectId":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\ObjectId("' . $var->__toString() . '")';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Int64":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\Int64(' . $var->__toString() . ')';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\UTCDateTime":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\UTCDateTime(' . $var->sec . ', ' . $var->usec . ')';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Regex":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\Regex(\'/' . $var->regex . '/' . $var->flags . '\')';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Timestamp":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\Timestamp(' . $var->sec . ', ' . $var->inc . ')';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\MinKey":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\MinKey()';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\MaxKey":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\MaxKey()';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Javascript":
					$this->_phpParams[$this->_paramIndex] = 'new MongoDB\BSON\Javascript("' . addcslashes($var->code, '"') . '", ' . var_export($var->scope, true) . ')';
					return $this->_param($this->_paramIndex);
				default:
					if (method_exists($var, "__toString")) {
						return $var->__toString();
					}
			}
		}
		return $var;
	}

	private function _formatVarAsJSON($var) {
		if (is_scalar($var) || is_null($var)) {
			switch (gettype($var)) {
				case "integer":
					$this->_paramIndex ++;
					$this->_jsonParams[$this->_paramIndex] = '' . $var;
					return $this->_param($this->_paramIndex);
				default:
					return $var;
			}
		}
		if (is_array($var)) {
			foreach ($var as $index => $value) {
				$var[$index] = $this->_formatVarAsJSON($value);
			}
			return $var;
		}
		if (is_object($var)) {
			$this->_paramIndex ++;
			switch (get_class($var)) {
				case "MongoDB\BSON\ObjectId":
					$this->_jsonParams[$this->_paramIndex] = 'ObjectId("' . $var->__toString() . '")';
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Int64":
					$this->_jsonParams[$this->_paramIndex] = $var->__toString();
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\UTCDateTime":
					$timezone = @date_default_timezone_get();
					date_default_timezone_set("UTC");
					//$this->_jsonParams[$this->_paramIndex] = "ISODate(\"" . date("Y-m-d", $var->sec) . "T" . date("H:i:s.", $var->sec) . ($var->usec/1000) . "Z\")";
					$time = $var->toDatetime();
					$this->_jsonParams[$this->_paramIndex] = "ISODate(\"" . $time->format('Y-m-d') . "T" . $time->format('H:i:s.') . (intval($var->__toString()) % 1000) ."Z\")";
					date_default_timezone_set($timezone);
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Timestamp":
					$this->_jsonParams[$this->_paramIndex] = json_encode(array(
						"t" => $var->getIncrement() * 1000,
						"i" => $var->getTimestamp()
					));
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\MinKey":
					$this->_jsonParams[$this->_paramIndex] = json_encode(array( '$minKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\MaxKey":
					$this->_jsonParams[$this->_paramIndex] = json_encode(array( '$maxKey' => 1 ));
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Javascript":
					$this->_jsonParams[$this->_paramIndex] = $var->__toString();
					return $this->_param($this->_paramIndex);
				case "MongoDB\BSON\Binary":
					$this->_jsonParams[$this->_paramIndex] = json_encode($var->jsonSerialize());
					return $this->_param($this->_paramIndex);
				default:
					if (method_exists($var, "__toString")) {
						return $var->__toString();
					}
					return '<unknown type>';
			}
		}
	}
}

?>
