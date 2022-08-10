<?php

class MCollection {
	public static function fields (MongoDB\Database $db, $collection) {
		$one = $db->selectCollection($collection)->findOne();
		if (empty($one)) {
			return array();
		}
		$fields = array();
		self::_fieldsFromRow($fields, $one);
		return $fields;
	}

	private static function _fieldsFromRow(&$fields, $row, $prefix = null) {
		foreach ($row as $field => $value) {
			if (is_integer($field) || is_float($field)) {
				continue;
			}
			$namespace = (is_null($prefix)) ? $field : $prefix . "." . $field;
			$fields[] = $namespace;
			if (is_array($value)) {
				self::_fieldsFromRow($fields, $value, $namespace);
			}
		}
	}

	/**
	 * If a row is GridFS row
	 *
	 * @param array $row record data
	 * @return boolean
	 */
	public static function isFile(array $row) {
		return isset($row["filename"]) && isset($row["chunkSize"]);
	}

	/**
	 * get .chunks collection name from .files collection name
	 *
	 * @param string $filesCollection
	 * @return string
	 */
	public static function chunksCollection($filesCollection) {
		return preg_replace("/\\.files$/", ".chunks", $filesCollection);
	}

	/**
	 * read collection information
	 *
	 * @param MongoDB $db database
	 * @param string $collection collection name
	 */
	public static function info(MongoDB\Database $db, $collection) {
		$isCapped = false;
		$size = 0;
		$max = 0;
		
		try{
			$it = $db->listCollections(['filter'=>['name'=>$collection]]);
			$info = iterator_to_array($it);
			$options = [];
			if(count($info) > 0) $options = $info[0]->getOptions();

			if (isset($options["capped"])) {
				$isCapped = $options["capped"];
			}
			if (isset($options["size"])) {
				$size = $options["size"];
			}
			if (isset($options["max"])) {
				$max = $options["max"];
			}

			
		} catch(Exception $e) {
			//exit("There is something wrong: <font color=\"red\">{$e->getMessage()}</font>, please refresh the page to try again.");
			//echo "There is something wrong: <font color=\"red\">{$e->getMessage()}</font>, please refresh the page to try again.";
		}
		
		return array( "capped" => $isCapped, "size" => $size, "max" => $max );
	}

	/**
	 * get collection documents size
	 *
	 * @param MongoDB $db database
	 * @param string $collection collection name
	 * @return int document count
	 */
	public static function countDocuments(MongoDB\Database $db, $collection) {
		$count = 0;
		try{
			$stat = $db->command(array("collStats" => $collection ))->toArray()[0];
			if(isset($stat['count'])) $count = $stat['count'];
		} catch(Exception $e) {
			//exit("There is something wrong: <font color=\"red\">{$e->getMessage()}</font>, please refresh the page to try again.");
		}
		
		return $count;
	}

	/**
	 * Create collection
	 *
	 * @param MongoDB $db MongoDB
	 * @param string $name Collection name
	 * @param array $options Options, capped, size, max
	 */
	public static function createCollection(MongoDB\Database $db, $name, array $options) {
		// TODO 这里要判断数据库是否已经存在
		$db->createCollection($name, $options);
	}
}

?>
