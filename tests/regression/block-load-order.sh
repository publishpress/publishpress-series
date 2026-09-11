#!/usr/bin/env bash

set -eu

ppseries_plugin_dir=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
ppseries_wp_root=$(CDPATH= cd -- "$ppseries_plugin_dir/../../.." && pwd)
ppseries_original_plugins=$(wp --path="$ppseries_wp_root" option get active_plugins --format=json)

ppseries_restore_plugins() {
    wp --path="$ppseries_wp_root" option update active_plugins "$ppseries_original_plugins" --format=json >/dev/null
}

trap ppseries_restore_plugins EXIT

ppseries_without_series=$(printf '%s' "$ppseries_original_plugins" | jq -c '
    [.[] | select(
        . != "publishpress-series/orgSeries.php"
        and . != "publishpress-series-pro/publishpress-series-pro.php"
    )]
')

ppseries_check_order() {
    ppseries_label=$1
    ppseries_plugins=$2

    wp --path="$ppseries_wp_root" option update active_plugins "$ppseries_plugins" --format=json >/dev/null
    wp --path="$ppseries_wp_root" eval '
        $block = WP_Block_Type_Registry::get_instance()->get_registered("publishpress-series/series-list");
        if (! $block) {
            fwrite(STDERR, "Series List block is not registered.\n");
            exit(1);
        }

        $assets = [
            "script" => [wp_scripts(), "js"],
            "style"  => [wp_styles(), "css"],
        ];
        foreach ($assets as $type => [$registry, $extension]) {
            $actual = isset($registry->registered["publishpress-series-blocks"])
                ? $registry->registered["publishpress-series-blocks"]->src
                : "";
            $expected = plugins_url(
                "assets/{$extension}/publishpress-series-blocks.{$extension}",
                WP_PLUGIN_DIR . "/publishpress-series/orgSeries.php"
            );

            if ($expected !== $actual) {
                fwrite(STDERR, "Unexpected block {$type} URL.\nExpected: {$expected}\nActual:   {$actual}\n");
                exit(1);
            }
        }
    '

    printf '%s load order passed.\n' "$ppseries_label"
}

ppseries_free_first=$(printf '%s' "$ppseries_without_series" | jq -c '
    . + [
        "publishpress-series/orgSeries.php",
        "publishpress-series-pro/publishpress-series-pro.php"
    ]
')
ppseries_check_order "Free-first" "$ppseries_free_first"

ppseries_pro_first=$(printf '%s' "$ppseries_without_series" | jq -c '
    . + [
        "publishpress-series-pro/publishpress-series-pro.php",
        "publishpress-series/orgSeries.php"
    ]
')
ppseries_check_order "Pro-first" "$ppseries_pro_first"
