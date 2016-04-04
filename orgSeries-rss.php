<?php
//This file contains all the feed related functions for organize series

//add_actions for rss/atom
add_action('rss2_item', 'the_series_rss');
add_action('atom_entry', 'the_series_atom');
add_action('rss2_ns','series_ns');
add_action('atom_ns', 'series_ns');

function get_series_rss_link($echo = false, $series_id = '') {
	$permalink_structure = get_option('permalink_structure');
	
	//check for series_id and attempt to find it if possible
	if ( $series_id == '' ) {
		$series = get_query_var(SERIES_QUERYVAR);
	}
	
	if ( $series_id == '' ) return;
	
	if ( '' == $permalink_structure ) {
		$link = get_option('home') . '?feed=rss2&amp;' . SERIES_QUERYVAR . '=' . $series_id;
	} else {
		$link = get_series_link($series_id);
		$link = trailingslashit($link) . user_trailingslashit('feed', 'feed');
	}
	
	$link = apply_filters('series_feed_link', $link);
	
	if ( $echo )
		echo $link;
	return $link;
}

function get_the_series_rss($type = 'rss') {
	$series = get_the_series();
	$the_list = '';
	$series_names = array();
	
	$filter = 'rss';
	if ( 'atom' == $type )
		$filter = 'raw';
		
	if ( !empty($series) ) foreach ( (array) $series as $serial ) {
		$series_names[] = sanitize_term_field('name', $serial->name, $serial->term_id, 'series', $filter);
	}
	
	$series_names = array_unique($series_names);
	
	foreach ( $series_names as $series_name ) {
		if ( 'rdf' == $type )
			$the_list .= "\n\t\t<series:name><![CDATA[$series_name]]></series:name>\n";
		elseif ( 'atom' == $type )
			$the_list .= sprintf( '<series:name scheme="%1$s" term="%2$s" />' , esc_attr( apply_filters( 'get_bloginfo_rss', get_bloginfo( 'url' ) ) ), esc_attr( $series_name ) );
		else
			$the_list .= "\n\t\t<series:name><![CDATA[$series_name]]></series:name>\n";
	}
	
	return apply_filters('the_series_rss', $the_list, $type);
}


function the_series_rss($type = 'rss') {
	echo get_the_series_rss($type);
}

function the_series_atom() {
	echo get_the_series_rss('atom');
}

function series_ns() {
	$ns = 'xmlns:series="http://organizeseries.com/"' . "\n\t";
	echo $ns;
}
?>