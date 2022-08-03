<?php

function formatJsonQuery($query) {
	$mgr = new LegalQuery($query);
	$q = $mgr->trimComma()
			 ->autoQuote()
			 ->dealRegex()
			 ->rmTypeInfo('NumberInt')
			 ->rmTypeInfo('NumberLong')
			 ->rmTypeInfo('ObjectId')
			 ->build();
	return $q;
}

class LegalQuery {
	private $_q = '';
	
	public function __construct($query) {
		$this->_q = $query;
	}

	public function trimComma() {
		$pattern = '/,\s*}\s*$/';
		$this->_q = preg_replace($pattern, '}', $this->_q, -1);
		return $this;
	}

	// for regex query
	public function dealRegex() {
		$pattern = '/:\s*\/(.*)\/\s*([,}])/';
		$this->_q = preg_replace($pattern, ': {"$regex":"$1"}$2', $this->_q, -1);
		return $this;

	}

	public function dealNLCR() {
		$this->_q = str_replace(["/r/n", "/r", "/n", PHP_EOL], "", $this->_q);
		return $this;
	}

	// auto add quote for key
	public function autoQuote() {
		$pattern = '/([{,])\s*([\$\w]*)\s*:/';
		$this->_q = preg_replace($pattern, '$1"$2":', $this->_q, -1);
		return $this;
	}

	public function rmTypeInfo($type) {
		if ($type == "ObjectId"){
			$pattern = '/'.$type.'[ ]*\(\"(.*?)\"[ ]*\)/';
			$this->_q = preg_replace($pattern, '"obj_${1}"', $this->_q, -1);
		} else {
			$pattern = '/'.$type.'[ ]*\((.*?)[ ]*\)/';
			$this->_q = preg_replace($pattern, '${1}', $this->_q, -1);
		}
		return $this;
	}

	public function build() {
		return $this->_q;
	}
}

function runTestCase() {
	$cases = [
		'{Name:/张/}' => '{"Name": {"$regex":"张"}}',
		'{Name :/ 张/}' => '{"Name": {"$regex":" 张"}}',
		'{Name:"6"}' => '{"Name":"6"}',
		'{"Name" :6}' => '{"Name" :6}',
		'{Name: 6}' => '{"Name": 6}',
		'{ Count:{$gt:5} }' => '{"Count":{"$gt":5} }',
		'{Count:{$gt:5}}' => '{"Count":{"$gt":5}}',
		'{Count:{"$gt":5}}' => '{"Count":{"$gt":5}}',
		'{Name:"http://abc.dj.com/a/sss.mp3"}' => '{"Name":"http://abc.dj.com/a/sss.mp3"}',
		'{ Type: 1, $or: [ { TUid: 1709768 }, { FUid: 1709768 } ] }' => '{"Type": 1,"$or": [ {"TUid": 1709768 }, {"FUid": 1709768 } ] }',
		'{Type:1, $or:[{TUid:1709768},{FUid:1709768}]}' => '{"Type":1,"$or":[{"TUid":1709768},{"FUid":1709768}]}',
	];

	$totalCnt = count($cases);
	$errCnt = 0;
	foreach($cases as $src=>$pred) {
		$result = formatJsonQuery($src);
		if($result == $pred) {
			echo "[PASS]   $src\n";
		} else {
			echo "[FAILED] $src, RESULT: $result, PRED: $pred\n";
			$errCnt++;
		}
	}
	echo "Summary: Total $totalCnt CASES, $errCnt FAILED.\n";
}

//runTestCase();
