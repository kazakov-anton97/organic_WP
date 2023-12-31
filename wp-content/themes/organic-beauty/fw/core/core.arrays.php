<?php
/**
 * Organic Beauty Framework: arrays manipulations
 *
 * @package	organic_beauty
 * @since	organic_beauty 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }

//  Return list <option value='id'>name</option> as string from two-dim array
if (!function_exists('organic_beauty_array_get_list_options')) {
	function organic_beauty_array_get_list_options($arr, $cur) {
		$rezList = "";
		if (is_array($arr) && count($arr) > 0) {
			foreach ($arr as $k=>$v) {
				$rezList .= "\n".'<option value="'.esc_attr($k).'"'.($cur==$k ? ' selected="selected">' : '>').esc_html($v).'</option>';
			}
		}
		return $rezList;
	}
}

//  Return 'id' by key from two-dim array
if (!function_exists('organic_beauty_array_get_id_by_key')) {
	function organic_beauty_array_get_id_by_key($curKey, $arr) {
		return (isset($arr[$curKey]) ? $arr[$curKey]['id'] : 0);
	}
}

//  Return key 'name' by key 'id'
if (!function_exists('organic_beauty_array_get_name_by_id')) {
	function organic_beauty_array_get_name_by_id($curId, $arr) {
		$rez = '';
		if (is_array($arr) && count($arr) > 0) {
			foreach ($arr as $k=>$v) {
				if ($arr[$k]['id']==$curId) {
					$rez = $arr[$k]['name'];
					break;
				}
			}
		}
		return $rez;
	}
}

// Merge arrays and lists (preserve number indexes)
if (!function_exists('organic_beauty_array_merge')) {
	function organic_beauty_array_merge($a1, $a2) {
		for ($i = 1; $i < func_num_args(); $i++){
			$arg = func_get_arg($i);
			if (is_array($arg) && count($arg)>0) {
				foreach($arg as $k=>$v) {
					$a1[$k] = $v;
				}
			}
		}
		return $a1;
	}
}

// Inserts any number of scalars or arrays at the point
// in the haystack immediately after the search key ($needle) was found,
// or at the end if the needle is not found or not supplied.
// Modifies $haystack in place.
if (!function_exists('organic_beauty_array_insert')) {
	function organic_beauty_array_insert_after(&$haystack, $needle, $stuff){
		if (! is_array($haystack) ) return -1;

		$new_array = array();
		for ($i = 2; $i < func_num_args(); ++$i){
			$arg = func_get_arg($i);
			if (is_array($arg)) {
				if ($i==2)
					$new_array = $arg;
				else
					$new_array = organic_beauty_array_merge($new_array, $arg);
			} else 
				$new_array[] = $arg;
		}

		$i = 0;
		if (is_array($haystack) && count($haystack) > 0) {
			foreach($haystack as $key => $value){
				$i++;
				if ($key == $needle) break;
			}
		}

		$haystack = organic_beauty_array_merge(array_slice($haystack, 0, $i, true), $new_array, array_slice($haystack, $i, null, true));

		return $i;
    }
}

// Inserts any number of scalars or arrays at the point
// in the haystack immediately before the search key ($needle) was found,
// or at the end if the needle is not found or not supplied.
// Modifies $haystack in place.
if (!function_exists('organic_beauty_array_before')) {
	function organic_beauty_array_insert_before(&$haystack, $needle, $stuff){
		if (! is_array($haystack) ) return -1;

		$new_array = array();
		for ($i = 2; $i < func_num_args(); ++$i){
			$arg = func_get_arg($i);
			if (is_array($arg)) {
				if ($i==2)
					$new_array = $arg;
				else
					$new_array = organic_beauty_array_merge($new_array, $arg);
			} else 
				$new_array[] = $arg;
		}

		$i = 0;
		if (is_array($haystack) && count($haystack) > 0) {
			foreach($haystack as $key => $value){
				if ($key == $needle) break;
				$i++;
			}
		}

		$haystack = organic_beauty_array_merge(array_slice($haystack, 0, $i, true), $new_array, array_slice($haystack, $i, null, true));

		return $i;
    }
}
?>