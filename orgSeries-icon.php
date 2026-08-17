<?php
##SERIES-ICON RELATED STUFF

function default_seriesicons_upload() {
	$def_path = apply_filters( 'orgseries_icons_path', ABSPATH );
	$def_url = apply_filters( 'orgseries_icons_url', home_url() . '/' );
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

    $row = $wpdb->get_row( $wpdb->prepare("SELECT icon FROM $tablename WHERE term_id=%d", $series) );

	if ($row ) {
		return $row->icon;
	} else  {
        return false;
    }
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
		foreach(array_keys($arr) as $k) {
			$arr[$k] = stripslashes($arr[$k]);
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

	if ( empty($series)  || '' == $series || empty($icon) || '' == $icon ){
        return false;
    }

	if ($wpdb->get_var( $wpdb->prepare("SELECT term_id FROM `$tablename` WHERE term_id=%d", $series) ) ) {
		$result = $wpdb->update( $tablename, array('icon' => $icon), array('term_id' => $series), array('%s'), array('%d') );
	} else {
        $result = $wpdb->insert($tablename, array('icon' => $icon, 'term_id' => $series), array('%s','%d'));
	}

	/**
	 * $wpdb::update() returns 0 when the stored value is already the same, which
	 * is not a failure. Only false means the query itself did not run.
	 */
	if ( false === $result ) {
		seriesicons_write_error( $wpdb->last_error );
		return false;
	}

	return true;
}

/**
* Record a failed series icon write so that seriesicons_write_error_notice() can report it.
* @param string $db_error The error message reported by $wpdb.
*/
function seriesicons_write_error($db_error = '') {
	if ( ! function_exists('set_transient') || ! is_admin() ) {
		return;
	}

	set_transient( 'pp_series_icon_write_error_' . get_current_user_id(), (string) $db_error, 5 * MINUTE_IN_SECONDS );
}

/**
* Tell the user that the series icon was not saved, instead of failing silently.
*/
function seriesicons_write_error_notice() {
	$transient_key = 'pp_series_icon_write_error_' . get_current_user_id();
	$db_error = get_transient( $transient_key );

	if ( false === $db_error ) {
		return;
	}

	delete_transient( $transient_key );

	$message = __('PublishPress Series could not save the series featured image. The featured image was not changed.', 'organize-series');

	if ( '' !== $db_error ) {
		/* translators: %s: the error message reported by the database. */
		$message .= ' ' . sprintf( __('Database error: %s', 'organize-series'), $db_error );
	}

	printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
}
add_action( 'admin_notices', 'seriesicons_write_error_notice' );

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

	$result = $wpdb->delete( $tablename, array('term_id' => $series), array('%d') );

	return false !== $result;
}
?>
