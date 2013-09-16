<?php
##SERIES-ICON RELATED STUFF

function default_seriesicons_upload() {
	$def_path = ABSPATH; 
	$def_url = trailingslashit(get_bloginfo('wpurl'));
	return array($def_path, $def_url);
}

/**
* Get series icons from database
* @param int $series Series ID
* @return icon url
*/
function series_get_icons($series) {
	global $wpdb;
	$tablename = $wpdb->prefix . 'orgseriesicons';
	
	if ($row = $wpdb->get_row( $wpdb->prepare("SELECT icon FROM $tablename WHERE term_id=%d", $series) ) ) {
		return $row->icon;
	} else return false;
}

/**
* Get series path and url (next two functions)
* function seriesicons_path
*	@return path of series icons
* function seriesicons_url
*	@return path of series urls
*/

function seriesicons_path() {
	$def = default_seriesicons_upload();
	return $def[0];
}

function seriesicons_url() {
	$def = default_seriesicons_upload();
	return $def[1];
}

/**
* Utility function to compute a rectangle to fit a given rectangle by maintaining the aspect ration.
* @return array containing computed height and width
*/
function series_fit_rect($width, $height, $max_width=-1, $max_height=-1, $expand=false) {
	$h = $height;
	$w = $width;
	if ($max_width>0 && ($w > $max_width || $expand)) {
		$w = $max_width;
		$h = floor(($w*$height)/$width);
	}
	if ($max_height>0 && $h>$max_height) {
		$h = $max_height;
		$w = floor(($h*$width)/$height);
	}
	return array($w,$h);
}

/**
* Utility function to take in a referenced variable and sanitize the contents.  First seen in the category-icons plugin by Ivan Georgiev (ivan@georgievi.net)
*/
if (!function_exists('stripslaghes_gpc_arr')) {
function stripslaghes_gpc_arr(&$arr) {
	if (get_magic_quotes_gpc()) {
		foreach(array_keys($arr) as $k) $arr[$k] = stripslashes($arr[$k]);
	}
}
}

/**
* Database write function to add the series icon/series relationship to the database
* @param int $series Series ID
* @param string $icon Series icon
* @return boolean true if db write is successful
*/
function seriesicons_write($series, $icon) {
	global $wpdb;
	$tablename = $wpdb->prefix . 'orgseriesicons'; 
	
	if ( empty($series)  || '' == $series || empty($icon) || '' == $icon )	return false;
		
	if ($wpdb->get_var( $wpdb->prepare("SELECT term_id FROM $tablename WHERE term_id=%d", $series) ) ) {
			
		$wpdb->query( $wpdb->prepare("UPDATE $tablename SET icon=%s WHERE term_id=%d", $icon, $series) );
	} else {
		$wpdb->query( $wpdb->prepare("INSERT INTO $tablename (term_id, icon) VALUES (%d,%s)", $series, $icon) );
	
	}
	return true;
}

/**
* Database delete function to remove the series icon/series relationship from the database.
* @param int $series Series ID
* @param string $icon Series Icon
* @return boolean true if db delete is successful
*/
function seriesicons_delete($series) {
	global $wpdb;
	$tablename = $wpdb->prefix . 'orgseriesicons';
	
	if ( empty($series)  || '' == $series  )	return false;

	$wpdb->query( $wpdb->prepare("DELETE FROM $tablename WHERE term_id=%d", $series) );
	return true;
}
?>