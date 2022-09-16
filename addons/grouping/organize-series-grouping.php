<?php
$orgseries_groups_ver = '2.2.7.rc.000';
global $orgseries_groups_ver;

$os_grouping_path = PPSERIES_PATH.'addons/grouping/';
define('OS_GROUPING_VERSION', $orgseries_groups_ver);

//fallback on loading legacy-includes.php in case the bootstrapped stuff isn't ready yet.
if (! defined('OS_GROUPING_LEGACY_LOADED')) {
    require_once $os_grouping_path . 'legacy-includes.php';
}
