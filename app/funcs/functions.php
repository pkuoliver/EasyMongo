<?php

/**
 * Convert unicode in json to utf-8 chars
 *
 * @param string $json String to convert
 * @return string utf-8 string
 */
function json_unicode_to_utf8($json) {
	//echo $json;
	$json = preg_replace_callback("/\\\\u([0-9a-f]{4})/", function($match) {
		$val = intval($match[1], 16);
		$c = "";
		if($val < 0x7F){        // 0000-007F
			$c .= chr($val);
		} elseif ($val < 0x800) { // 0080-0800
			$c .= chr(0xC0 | intval($val / 64));
			$c .= chr(0x80 | ($val % 64));
		} else {                // 0800-FFFF
			$c .= chr(0xE0 | intval(($val / 64) / 64));
			$c .= chr(0x80 | (intval($val / 64) % 64));
			$c .= chr(0x80 | intval($val % 64));
		}
		//if($val<=0x7F)       return chr($val);
		//if($val<=0x7FF)      return chr(($val>>6)+192).chr(($val&63)+128);
		//if($val<=0xFFFF)     return chr(($val>>12)+224).chr((($val>>6)&63)+128).chr(($val&63)+128);
		//if($val<=0x1FFFFF)   return chr(($val>>18)+240).chr((($val>>12)&63)+128).chr((($val>>6)&63)+128).chr(($val&63)+128);
		return $c;
	}, $json);
	return $json;
}

/**
 * Format JSON to pretty html
 *
 * @param string $json JSON to format
 * @return string
 */
function json_format_html($json) {
	$json = json_unicode_to_utf8($json);
	$tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
	$new_json = "";
	$indent_level = 0;
	$in_string = false;

	$len = strlen($json);
	for($c = 0; $c < $len; $c++) {
		$char = $json[$c];
		switch($char) {
			case '{':
			case '[':
				$char = "<font color=\"green\">" . $char . "</font>";//iwind
				if(!$in_string) {
					$new_json .= $char . "<br/>" . trim(str_repeat($tab, $indent_level+1));
					$indent_level++;
				} else {
					$new_json .= "[";
				}
				break;
			case '}':
			case ']':
				$char = "<font color=\"green\">" . $char . "</font>";//iwind
				if(!$in_string) {
					$indent_level--;
					$new_json .= "<br/>" . str_repeat($tab, $indent_level) . $char;
				} else {
					$new_json .= "]";
				}
				break;
			case ',':
				$char = "<font color=\"green\">" . $char . "</font>";//iwind
				if(!$in_string) {
					$new_json .= ",<br/>" . str_repeat($tab, $indent_level);
				} else {
					$new_json .= ",";
				}
				break;
			case ':':
				$char = "<font color=\"green\">" . $char . "&nbsp;</font>";//iwind
				if($in_string) {
					$new_json .= ":";
				} else {
					$new_json .= $char;
				}
				break;
			case '"':
				if($c > 0 && $json[$c-1] != '\\') {
					$in_string = !$in_string;
					if ($in_string) {
						$new_json .= "<font color=\"#DD0000\" class=\"string_var\">" . $char;
					} else {
						$new_json .= $char . "</font>";
					}
	   				break;
				} else if ($c == 0) {
					$in_string = !$in_string;
					$new_json .= "<font color=\"red\">" . $char;
					break;
				}
			default:
				if (!$in_string && trim($char) !== "") {
					$char = "<font color=\"blue\">" . $char . "</font>";
				} else {
					if ($char == "&" || $char == "'" || $char == "\"" || $char == "<" || $char == ">") {
						$char = htmlspecialchars($char);
					}
				}
				//去掉引号前面的空格，以免影响排版
				if($in_string || (!$in_string && trim($char) != ""))  $new_json .= $char;
				//$new_json .= $char;
				break;
		}
	}
	$new_json = preg_replace_callback("{(<font color=\"blue\">([\\da-zA-Z_\\.]+)</font>)+}", function($match){
		$string = str_replace("<font color=\"blue\">", "", $match[0]);
		$string = str_replace("</font>", "", $string);
		return "<font color=\"blue\" class=\"no_string_var\">" . $string  . "</font>";
	}, $new_json);
	return $new_json;
}


/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Format JSON to pretty style
 *
 * @param string $json JSON to format
 * @return string
 */
function json_format($json) {
	$tab = "  ";
	$new_json = "";
	$indent_level = 0;
	$in_string = false;
	$len = strlen($json);

	for($c = 0; $c < $len; $c++) {
		$char = $json[$c];
		switch($char) {
			case '{':
			case '[':
				if(!$in_string) {
					$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
					$indent_level++;
				} else {
					$new_json .= $char;
				}
				break;
			case '}':
			case ']':
				if(!$in_string) {
					$indent_level--;
					$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
				} else {
					$new_json .= $char;
				}
				break;
			case ',':
				if(!$in_string) {
					$new_json .= ",\n" . str_repeat($tab, $indent_level);
				} else {
					$new_json .= $char;
				}
				break;
			case ':':
				if(!$in_string) {
					$new_json .= ": ";
				} else {
					$new_json .= $char;
				}
				break;
			case '"':
				if($c > 0 && $json[$c-1] != '\\') {
					$in_string = !$in_string;
				}
			default:
				$new_json .= $char;
				break;
		}
	}

	return $new_json;
}

/**
 * Format bytes to human size
 *
 * @param integer $bytes Size in byte
 * @param integer $precision Precision
 * @return string size in k, m, g..
 * @since 1.1.7
 */
function r_human_bytes($bytes, $precision = 2) {
	if ($bytes == 0) {
		return 0;
	}
	if ($bytes < 1024) {
		return $bytes . "B";
	}
	if ($bytes < 1024 * 1024) {
		return round($bytes/1024, $precision) . "KB";
	}
	if ($bytes < 1024 * 1024 * 1024) {
		return round($bytes/1024/1024, $precision) . "MB";
	}
	if ($bytes < 1024 * 1024 * 1024 * 1024) {
		return round($bytes/1024/1024/1024, $precision) . "GB";
	}
	return $bytes;
}

function r_human_duration($sec) {
	if ($sec < 60) {
		return $sec . " seconds";
	} elseif ($sec < 60 * 60) {
		return round($sec/(60)) . ' minitues, ' . $sec%(60) . " seconds";
	} elseif ($sec < 24 * 60 * 60) {
		return round($sec/(60*60)) . ' hours, ' . round($sec%(60*60)/(60)) . " minitues";
	} else {
		return round($sec/(24*60*60)) . ' days, ' . round($sec%(24*60*60)/(60*60)) . " hours";
	}
}

/**
 * Get collection display icon
 *
 * @param string $collectionName Collection name
 * @return string
 * @since 1.1.8
 */
function r_get_collection_icon($collectionName) {
	if (preg_match("/\\.(files|chunks)$/", $collectionName)){
		return "grid";
	}
	if (preg_match("/^system\\.js$/", $collectionName)) {
		return "table-systemjs";
	}
	return "table";
}

function mongo_object_to_array($mongoObj) {
	$arr = (array)$mongoObj;
	foreach($arr as $k=>$v) {
		return $v;
	}
	return NULL;
}

?>
