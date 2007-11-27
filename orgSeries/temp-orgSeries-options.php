<?php
//basic outline for code that needs to go here - for the organize series options page.
/*
** check if this is an init load or a submit or reset
** do a test for whether there's an earlier version installed.  IF so, give option to import series and options from earlier version.  
** If first install of orgSeries -> initialize default options.
** do i want separate pages for options?
** after setting this up I will have to do an overhal of the postlist display and the series meta code.
*/
<?php
function org_series_import() {
	//this will contain the actual query and code for importing from version 1.6 -> 2.0.  It is called from org_series_init if conditions for it being called are met.
	##option - import series from earlier version (transfer from category schema to new series schema)
		##option - delete category associations after import?
	##option - import category-icons from category icon plugin into new series table schema (after import be sure to erase old category-icon tables)
	##option - import options from previous plugin (after import be sure to erase old option tables)
}
function org_series_init($reset = false) {
//is this an overwrite of a previous version?  If so, does this require updates/imports from previous versions (less than 2.0)? provide the form for user to select whether they want to import from previous version.
	if (!$reset) $settings = get_option('org_series_options');
	
	if (!($is_initialized=get_option('org_series_is_initialized')) || empty ($settings) || $reset) {
		$init_settings = array( //options for the orgSeries plugin
		//main settings
			'custom_css' => 1, 
			'

}