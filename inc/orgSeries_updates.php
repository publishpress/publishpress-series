<?php
if ( !class_exists('PluginUpdateChecker') ):
	
/**
 * A custom plugin update checker. 
 * 
 * @author Janis Elsts
 * @modified by Darren Ethier
 * @copyright 2010
 * @version 1.0
 * @access public
 */
class PluginUpdateChecker {
	
	public $metadataUrl = ''; //The URL of the plugin's metadata file.
	public $pluginFile = '';  //Plugin filename relative to the plugins directory.
	public $slug = '';        //Plugin slug. (with .php extension)
	public $checkPeriod = 12; //How often to check for updates (in hours).
	public $optionName = '';  //Where to store the update info.
	public $json_error = ''; //for storing any json_error data that get's returned so we can display an admin notice.
	public $orgseries_api = ''; //used to hold the user API.  If not set then nothing will work!
	public $install_key = ''; //used to hold the install_key if set.
	public $download_query = ''; //used to hold the query variables for download checks;
	public $lang_domain; //used to hold the localization domain for translations (get from $orgseries object).
	public $dismiss_upgrade; //for setting the dismiss upgrade option (per plugin).
	
	/**
	 * Class constructor.
	 * 
	 * @param string $metadataUrl The URL of the plugin's metadata file.
	 * @param string $pluginFile Fully qualified path to the main plugin file.
	 * @param string $slug The plugin's 'slug'. If not specified, the filename part of $pluginFile sans '.php' will be used as the slug.
	 * @param integer $checkPeriod How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
	 * @param string $optionName Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'. 
	 * @return void
	 */
	function __construct($metadataUrl, $slug = '', $checkPeriod = 12, $optionName = ''){
		$this->metadataUrl = $metadataUrl;
		$this->checkPeriod = $checkPeriod;
		$this->slug = $slug;
		$tr_slug = str_replace('-','_',$this->slug);
		$this->pluginFile = $slug.'/'.$slug.'.php';
		$this->optionName = $optionName;
		$this->dismiss_upgrade = 'os_dismissed_upgrade_'.$tr_slug;
		
		//If no slug is specified, use the name of the main plugin file as the slug.
		//For example, 'my-cool-plugin/cool-plugin.php' becomes 'cool-plugin'.
		if ( empty($this->slug) ){
			$this->slug = basename($this->pluginFile, '.php');
		}
		
		if ( empty($this->optionName) ){
			$this->optionName = 'external_updates-' . $this->slug;
		}
		$this->set_api();
		$this->installHooks();		
	}
	
	/**
	* sets the domain for localization
	**/
	function set_domain() {
		global $orgseries;
		if ( empty($orgseries) && class_exists('orgSeries') ) {
			$orgseries = new orgSeries();
		}
		$this->lang_domain = 'organize-series';
	}
	/**
	* gets the api from the options table if present
	**/
	function set_api($new_api = '') {
		$series_settings = get_option('org_series_options');
			
		if ( $install_key = get_option('orgseries_install_key') )
			$this->install_key = $install_key;
			$download_i = 'orgseries_install_key='.$install_key;
			
		if ( !empty($new_api) ) {
			$this->orgseries_api = $new_api;
			$download = 'orgseries_plugin_api='.$this->orgseries_api;
			$this->download_query = ( !empty($download_i) ) ? $download.'&'.$download_i : $download;
			return;
		}
		
		if (!empty($series_settings['orgseries_api'])) {
			$this->orgseries_api = $series_settings['orgseries_api'];
			$download = 'orgseries_plugin_api='.$this->orgseries_api;
			$this->download_query = ( !empty($download_i) ) ? $download.'&'.$download_i : $download;
			return;
		}
		
		$this->download_query = $download_i;
	}
	
	/**
	 * Install the hooks required to run periodic update checks and inject update info 
	 * into WP data structures. 
	 * Also other hooks related to the automatic updates (such as checking agains API and what not (@from Darren)
	 * @return void
	 */
	function installHooks(){
		//Override requests for plugin information
		add_filter('plugins_api', array(&$this, 'injectInfo'), 10, 3);
		
		//Insert our update info into the update array maintained by WP
		add_filter('site_transient_update_plugins', array(&$this,'injectUpdate')); //WP 3.0+
		//add_filter('transient_update_plugins', array(&$this,'injectUpdate')); //WP 2.8+
		
		//Set up the periodic update checks
		$cronHook = 'check_plugin_updates-' . $this->slug;
		if ( $this->checkPeriod > 0 ){
			
			//Trigger the check via Cron
			add_filter('cron_schedules', array(&$this, '_addCustomSchedule'));
			if ( !wp_next_scheduled($cronHook) && !defined('WP_INSTALLING') ) {
				$scheduleName = 'every' . $this->checkPeriod . 'hours';
				wp_schedule_event(time(), $scheduleName, $cronHook);
			}
			add_action($cronHook, array(&$this, 'checkForUpdates'));
			
			//In case Cron is disabled or unreliable, we also manually trigger 
			//the periodic checks while the user is browsing the Dashboard. 
			add_action( 'admin_init', array(&$this, 'maybeCheckForUpdates') );
			
		} else {
			//Periodic checks are disabled.
			wp_clear_scheduled_hook($cronHook);
		}
		//dashboard message "dismiss upgrade" link
		add_action( "wp_ajax_".$this->dismiss_upgrade, array(&$this, 'dashboard_dismiss_upgrade'));
		
		//add in api option on Series Options page if it's not already there.
		add_action('admin_init', array(&$this, 'orgseries_api'));
	}
	
	function orgseries_api() {
		$this->set_domain();		
		add_settings_field('orgseries_api_settings', 'Organize Series User API', array(&$this,'orgseries_api_output'), 'orgseries_options_page', 'series_automation_settings');
		register_setting('orgseries_options', 'org_series_options');
		add_filter('orgseries_options', array(&$this,'orgseries_api_validate'), 10, 2);
	}
	
	function orgseries_api_validate($newinput, $input) {
		if ( empty($newinput) || !is_array($newinput) ) $newinput = array();
		$newinput['orgseries_api'] = trim($input['orgseries_api']);
		$this->set_api($newinput['orgseries_api']);
		return $newinput;
	}
	
	function orgseries_api_output() {
		global $orgseries;
		$org_opt = $orgseries->settings;
		$org_name = 'org_series_options';
		?>
			<strong><?php _e('Organize Series API: ', $this->lang_domain); ?></strong>
			<input name="<?php echo $org_name; ?>[orgseries_api]" id="orgseries_api" type="text" value="<?php echo trim($org_opt['orgseries_api']); ?>"  /><br /><br />
		<?php
	}
	
	/**
	 * Add our custom schedule to the array of Cron schedules used by WP.
	 * 
	 * @param array $schedules
	 * @return array
	 */
	function _addCustomSchedule($schedules){
		if ( $this->checkPeriod && ($this->checkPeriod > 0) ){
			$scheduleName = 'every' . $this->checkPeriod . 'hours';
			$schedules[$scheduleName] = array(
				'interval' => $this->checkPeriod * 3600, 
				'display' => sprintf('Every %d hours', $this->checkPeriod),
			);
		}		
		return $schedules;
	}
	
	/**
	 * Retrieve plugin info from the configured API endpoint.
	 * 
	 * @uses wp_remote_get()
	 * 
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return orgseries_PluginInfo
	 */
	function requestInfo($queryArgs = array()){
		//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
		$queryArgs['installed_version'] = $this->getInstalledVersion(); 
		$queryArgs['orgseries_request_plugin'] = $this->slug;
		if ( !empty($this->install_key) )
			$queryArgs['orgseries_install_key'] = $this->install_key;
		if ( !empty($this->orgseries_api) )
			$queryArgs['orgseries_plugin_api'] = $this->orgseries_api;

		$queryArgs = apply_filters('puc_request_info_query_args-'.$this->slug, $queryArgs);
		
		//Various options for the wp_remote_get() call. Plugins can filter these, too.
		$options = array(
			'timeout' => 10, //seconds
			'headers' => array(
				'Accept' => 'application/json'
			),
		);
		$options = apply_filters('puc_request_info_options-'.$this->slug, array());
		
		//The plugin info should be at 'http://your-api.com/url/here/$slug/info.json'
		$url = $this->metadataUrl; 
		if ( !empty($queryArgs) ){
			$url = add_query_arg($queryArgs, $url);
		}
		
		$result = wp_remote_get(
			$url,
			$options
		);
		
		//Try to parse the response
		$pluginInfo = null;
		if ( !is_wp_error($result) && isset($result['response']['code']) && ($result['response']['code'] == 200) && !empty($result['body']) ){
			$pluginInfo = orgseries_PluginInfo::fromJson($result['body']);
		}
		$pluginInfo = apply_filters('puc_request_info_result-'.$this->slug, $pluginInfo, $result);
		return $pluginInfo;
	}
	
	/**
	 * Retrieve the latest update (if any) from the configured API endpoint.
	 * 
	 * @uses PluginUpdateChecker::requestInfo()
	 * 
	 * @return PluginUpdate An instance of PluginUpdate, or NULL when no updates are available.
	 */
	function requestUpdate(){
		//For the sake of simplicity, this function just calls requestInfo() 
		//and transforms the result accordingly.
		$pluginInfo = $this->requestInfo(array('checking_for_updates' => '1'));
		if ( $pluginInfo == null ){
			return null;
		}
		//admin display for if the update check reveals that there is a new version but the API key isn't valid.  
		if ( isset($pluginInfo->api_invalid) || isset($pluginInfo->no_api ) ) { //we have json_error returned let's display a message
			$this->json_error = $pluginInfo;
			add_action('admin_notices', array(&$this, 'display_json_error'));
			return null;
		}
		
		if ( isset($pluginInfo->new_install_key) ) {
			update_option('orgseries_install_key', $pluginInfo->new_install_key);
		}
		
		//need to correct the download url so it contains the custom user data (i.e. api and install key)
				
		if ( !empty($this->download_query) ) 
			$pluginInfo->download_url = $pluginInfo->download_url.'&'.$this->download_query;
		
		return PluginUpdate::fromPluginInfo($pluginInfo);
	}
	
	function display_json_error() {
		$pluginInfo = $this->json_error;
		$pluginName = ucwords(str_replace('-', ' ', $this->slug ));
		$update_dismissed = get_option($this->dismiss_upgrade);
		
		$is_dismissed = !empty($update_dismissed) && in_array($pluginInfo->version, $update_dismissed);
		
		if ($is_dismissed)
			return;
		
		//only display messages if there is a new version of the plugin.
		if ( version_compare($pluginInfo->version, $this->getInstalledVersion(), '>') ) {
			if ( $pluginInfo->no_api ){
				$msg = sprintf(__('<p>There is an automatic update for %s available but your api-key has not been set.  Please go to the <a href="options-general.php?page=orgseries_options_page">Organize Series Options page</a> to set your api_key for this addon.</p>', $this->lang_domain), $pluginName);
			}
			
			if ( $pluginInfo->api_invalid ) {
				$msg = sprintf(__('<p>There is an automatic update for %s available but your api-key is invalid.  Either you have entered the wrong key on the <a href="options-general.php?page=orgseries_options_page">Organize Series Options</a> page OR you no longer have access to the updates for this plugin.  Automatic upgrades for single website install\'s are gained via the <a href="http://organizeseries.com/pricing">Basic Support package</a>.  Automatic upgrades for multiple website install\'s are available via the <a href="http://organizeseries.com/pricing">All Addons package or the Lifetime Membership package.</a></p>', $this->lang_domain), $pluginName);
			}
			//Dismiss code below is obtained from the Gravity Forms Plugin by rocketgenius.com
			?>
				<div class="updated" style="padding:15px; position:relative;" id="orgseries_dashboard_message"><?php echo $msg ?>
				<a href="javascript:void(0);" onclick="OrgSeriesDismissUpgrade();" style='float:right;'><?php _e("Dismiss") ?></a>
            </div>
            <script type="text/javascript">
                function OrgSeriesDismissUpgrade(){
                    jQuery("#orgseries_dashboard_message").slideUp();
                    jQuery.post(ajaxurl, {action:"<?php echo $this->dismiss_upgrade; ?>", version:"<?php echo $pluginInfo->version; ?>", cookie: encodeURIComponent(document.cookie)});
                }
            </script>
			<?php
		}
	}
	
	function dashboard_dismiss_upgrade() {
		$os_ary = get_option($this->dismiss_upgrade);
		if (!is_array($os_ary))
			$os_ary = array();
		
		$os_ary[] = $_POST['version'];
		update_option($this->dismiss_upgrade, $os_ary);
	}
	
	/**
	 * Get the currently installed version of the plugin.
	 * 
	 * @return string Version number.
	 */
	function getInstalledVersion(){
		if ( function_exists('get_plugins') ) {
		$allPlugins = get_plugins();
		if ( array_key_exists($this->pluginFile, $allPlugins) && array_key_exists('Version', $allPlugins[$this->pluginFile]) ){
			return $allPlugins[$this->pluginFile]['Version']; 
		} else {
			return ''; //This should never happen.
		};
		}
		return ''; //this should never happen
	}
	
	/**
	 * Check for plugin updates. 
	 * The results are stored in the DB option specified in $optionName.
	 * 
	 * @return void
	 */
	function checkForUpdates(){
		$state = get_option($this->optionName);
		if ( empty($state) ){
			$state = new StdClass;
			$state->lastCheck = 0;
			$state->checkedVersion = '';
			$state->update = null;
		}
		
		$state->lastCheck = time();
		$state->checkedVersion = $this->getInstalledVersion();
		update_option($this->optionName, $state); //Save before checking in case something goes wrong 
		
		$state->update = $this->requestUpdate();
		update_option($this->optionName, $state);
	}
	
	/**
	 * Check for updates only if the configured check interval has already elapsed.
	 * 
	 * @return void
	 */
	function maybeCheckForUpdates(){
		if ( empty($this->checkPeriod) ){
			return;
		}
		
		$state = get_option($this->optionName);
	
		$shouldCheck =
			empty($state) ||
			!isset($state->lastCheck) || 
			( (time() - $state->lastCheck) >= $this->checkPeriod*3600 );
			
		if ( $shouldCheck ){
			$this->checkForUpdates();
		}
	}
	
	/**
	 * Intercept plugins_api() calls that request information about our plugin and 
	 * use the configured API endpoint to satisfy them. 
	 * 
	 * @see plugins_api()
	 * 
	 * @param mixed $result
	 * @param string $action
	 * @param array|object $args
	 * @return mixed
	 */
	function injectInfo($result, $action = null, $args = null){
    	$relevant = ($action == 'plugin_information') && isset($args->slug) && ($args->slug == $this->slug);
		if ( !$relevant ){
			return $result;
		}

		$pluginInfo = $this->requestInfo(array('checking_for_updates' => '1'));
		if ($pluginInfo){
			return $pluginInfo->toWpFormat();
		}
				
		return $result;
	}
	
	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 * 
	 * @param array $updates Update list.
	 * @return array Modified update list.
	 */
	function injectUpdate($updates){
		$state = get_option($this->optionName);
		
		//Is there an update to insert?
		if ( !empty($state) && isset($state->update) && !empty($state->update) ){
			//Only insert updates that are actually newer than the currently installed version.
			if ( version_compare($state->update->version, $this->getInstalledVersion(), '>') ){
				$updates->response[$this->pluginFile] = $state->update->toWpFormat();
			}
		}
				
		return $updates;
	}
	
	/**
	 * Register a callback for filtering query arguments. 
	 * 
	 * The callback function should take one argument - an associative array of query arguments.
	 * It should return a modified array of query arguments.
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback 
	 * @return void
	 */
	function addQueryArgFilter($callback){
		add_filter('puc_request_info_query_args-'.$this->slug, $callback);
	}
	
	/**
	 * Register a callback for filtering arguments passed to wp_remote_get().
	 * 
	 * The callback function should take one argument - an associative array of arguments -
	 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
	 * for details on what arguments are available and how they work. 
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback
	 * @return void
	 */
	function addHttpRequestArgFilter($callback){
		add_filter('puc_request_info_options-'.$this->slug, $callback);
	}
	
	/**
	 * Register a callback for filtering the plugin info retrieved from the external API.
	 * 
	 * The callback function should take two arguments. If the plugin info was retrieved 
	 * successfully, the first argument passed will be an instance of  orgseries_PluginInfo. Otherwise, 
	 * it will be NULL. The second argument will be the corresponding return value of 
	 * wp_remote_get (see WP docs for details).
	 *  
	 * The callback function should return a new or modified instance of orgseries_PluginInfo or NULL.
	 * 
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 * 
	 * @param callback $callback
	 * @return void
	 */
	function addResultFilter($callback){
		add_filter('puc_request_info_result-'.$this->slug, $callback, 10, 2);
	}
}
	
endif;

if ( !class_exists('orgseries_PluginInfo') ):

/**
 * A container class for holding and transforming various plugin metadata.
 * 
 * @author Janis Elsts
 * @copyright 2010
 * @version 1.0
 * @access public
 */
class orgseries_PluginInfo {
	//Most fields map directly to the contents of the plugin's info.json file.
	//See the relevant docs for a description of their meaning.  
	public $name;
	public $slug;
	public $version;
	public $homepage;
	public $sections;
	public $download_url;

	public $author;
	public $author_homepage;
	
	public $requires;
	public $tested;
	public $upgrade_notice;
	
	public $rating;
	public $num_ratings;
	public $downloaded;
	public $last_updated;
	
	public $id = 0; //The native WP.org API returns numeric plugin IDs, but they're not used for anything.
		
	/**
	 * Create a new instance of orgseries_PluginInfo from JSON-encoded plugin info 
	 * returned by an external update API.
	 * 
	 * @param string $json Valid JSON string representing plugin info. 
	 * @return orgseries_PluginInfo New instance of orgseries_PluginInfo, or NULL on error.
	 */
	public static function fromJson($json){
		$apiResponse = json_decode($json);
		if ( empty($apiResponse) || !is_object($apiResponse) ){
			return null;
		}
		
		//Very, very basic validation.
		$valid = (isset($apiResponse->name) && !empty($apiResponse->name) && isset($apiResponse->version) && !empty($apiResponse->version)) || (isset($apiResponse->api_invalid) || isset($apiResponse->no_api));
		if ( !$valid ){
			return null;
		}
		
		$info = new orgseries_PluginInfo();
		
		foreach(get_object_vars($apiResponse) as $key => $value){
			$info->$key = $value;
		}
		
		return $info;		
	}
	
	/**
	 * Transform plugin info into the format used by the native WordPress.org API
	 * 
	 * @return object
	 */
	public function toWpFormat(){
		$info = new StdClass;
		
		//The custom update API is built so that many fields have the same name and format
		//as those returned by the native WordPress.org API. These can be assigned directly. 
		$sameFormat = array(
			'name', 'slug', 'version', 'requires', 'tested', 'rating', 'upgrade_notice',
			'num_ratings', 'downloaded', 'homepage', 'last_updated',
		);
		foreach($sameFormat as $field){
			if ( isset($this->$field) ) {
				$info->$field = $this->$field;
			}
		}
		
		//Other fields need to be renamed and/or transformed.
		$info->download_link = $this->download_url;
		
		if ( !empty($this->author_homepage) ){
			$info->author = sprintf('<a href="%s">%s</a>', $this->author_homepage, $this->author);
		} else {
			$info->author = $this->author;
		}
		
		if ( is_object($this->sections) ){
			$info->sections = get_object_vars($this->sections);
		} elseif ( is_array($this->sections) ) {
			$info->sections = $this->sections;
		} else {
			$info->sections = array('description' => '');
		}
				
		return $info;
	}
}
	
endif;

if ( !class_exists('PluginUpdate') ):

/**
 * A simple container class for holding information about an available update.
 * 
 * @author Janis Elsts
 * @copyright 2010
 * @version 1.0
 * @access public
 */
class PluginUpdate {
	public $id = 0;
	public $slug;
	public $version;
	public $homepage;
	public $download_url;
	public $upgrade_notice;
	
	/**
	 * Create a new instance of PluginUpdate from its JSON-encoded representation.
	 * 
	 * @param string $json
	 * @return PluginUpdate
	 */
	public static function fromJson($json){
		//Since update-related information is simply a subset of the full plugin info,
		//we can parse the update JSON as if it was a plugin info string, then copy over
		//the parts that we care about.
		$pluginInfo = orgseries_PluginInfo::fromJson($json);
		if ( $pluginInfo != null ) {
			return PluginUpdate::fromPluginInfo($pluginInfo);
		} else {
			return null;
		}
	}
	
	/**
	 * Create a new instance of PluginUpdate based on an instance of orgseries_PluginInfo.
	 * Basically, this just copies a subset of fields from one object to another.
	 * 
	 * @param orgseries_PluginInfo $info
	 * @return PluginUpdate
	 */
	public static function fromPluginInfo($info){
		$update = new PluginUpdate();
		$copyFields = array('id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice');
		foreach($copyFields as $field){
			$update->$field = $info->$field;
		}
		return $update;
	}
	
	/**
	 * Transform the update into the format used by WordPress native plugin API.
	 * 
	 * @return object
	 */
	public function toWpFormat(){
		$update = new StdClass;
		
		$update->id = $this->id;
		$update->slug = $this->slug;
		$update->new_version = $this->version;
		$update->url = $this->homepage;
		$update->package = $this->download_url;
		if ( !empty($this->upgrade_notice) ){
			$update->upgrade_notice = $this->upgrade_notice;
		}
		
		return $update;
	}
}
	
endif;

?>