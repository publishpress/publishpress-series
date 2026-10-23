<?php

/**
 * Series part numbering across post status transitions.
 *
 * Run on a disposable site with PublishPress Series active (it creates series and posts):
 *   wp eval-file tests/regression/series-part-numbering.php
 *
 * Prints the parts after every step and checks that the counted (publish/private) posts of each
 * series are numbered 1..n with n equal to the series count, and that posts come back in order.
 * Ends with "FAILED CHECKS: 0" when everything passes.
 */
$GLOBALS['pp_fail'] = 0;
$tax = ppseries_get_series_slug();

function ppt_series($name) {
    $t = wp_insert_term($name . ' ' . wp_generate_password(4, false), ppseries_get_series_slug());
    return (int) $t['term_id'];
}
function ppt_post($title, $series_ids, $status = 'publish', $age = 0, $part = null) {
    $args = ['post_title' => $title, 'post_content' => "<p>$title</p>", 'post_status' => $status];
    if ($status === 'future') { $args['post_date'] = gmdate('Y-m-d H:i:s', time() + 86400); }
    else { $args['post_date'] = gmdate('Y-m-d H:i:s', time() - 3600 * (100 - $age)); }
    $id = wp_insert_post($args);
    if ($part !== null) { foreach ((array) $series_ids as $s) { update_post_meta($id, SERIES_PART_KEY . '_' . $s, $part); } }
    wp_set_post_terms($id, (array) $series_ids, ppseries_get_series_slug());
    return $id;
}
function ppt_show($label, $sid, $ids) {
    clean_term_cache($sid, ppseries_get_series_slug());
    $term = get_term($sid, ppseries_get_series_slug());
    $out = []; $parts = [];
    foreach ($ids as $name => $id) {
        clean_post_cache($id);
        $p = get_post($id);
        $part = $p ? get_post_meta($id, SERIES_PART_KEY . '_' . $sid, true) : 'deleted';
        $st = $p ? $p->post_status : 'deleted';
        $out[] = "$name=" . ($part === '' ? '-' : $part) . "(" . substr($st, 0, 4) . ")";
        if ($p && in_array($st, ['publish', 'private'], true) && has_term($sid, ppseries_get_series_slug(), $id)) { $parts[] = (int) $part; }
    }
    sort($parts);
    $ok = ($parts === range(1, max(1, count($parts))) || $parts === []) && count($parts) === (int) $term->count;
    if (!$ok) { $GLOBALS['pp_fail']++; }
    printf("  %-34s %s | total=%d %s\n", $label, implode(' ', $out), $term->count, $ok ? 'OK' : 'FAIL (parts ' . implode(',', $parts) . ')');
}

function ppt_expect($label, $sid, $ids, $expected) {
    $rows = [];
    foreach ($ids as $name => $id) {
        $p = get_post($id);
        if (!$p || !in_array($p->post_status, ['publish', 'private'], true)) { continue; }
        $rows[$name] = (int) get_post_meta($id, SERIES_PART_KEY . '_' . $sid, true);
    }
    asort($rows);
    $got = array_keys($rows);
    $ok = $got === $expected;
    if (!$ok) { $GLOBALS['pp_fail']++; }
    printf("  %-34s order %s %s\n", $label, implode(',', $got), $ok ? 'OK' : 'FAIL (expected ' . implode(',', $expected) . ')');
}

echo "S1 publish -> trash -> restore (draft) -> publish\n";
$s = ppt_series('S1'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S1 P$i", $s, 'publish', $i); }
ppt_show('5 published', $s, $ids);
wp_trash_post($ids['P2']); ppt_show('trash P2', $s, $ids);
wp_untrash_post($ids['P2']); ppt_show('restore P2 (core -> draft)', $s, $ids);
wp_update_post(['ID' => $ids['P2'], 'post_status' => 'publish']); ppt_show('publish P2', $s, $ids);
ppt_expect('S1 final', $s, $ids, ['P1','P2','P3','P4','P5']);

echo "S2 publish -> draft -> publish, and publish <-> private\n";
$s = ppt_series('S2'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S2 P$i", $s, 'publish', $i); }
wp_update_post(['ID' => $ids['P3'], 'post_status' => 'draft']); ppt_show('P3 -> draft', $s, $ids);
wp_update_post(['ID' => $ids['P5'], 'post_status' => 'pending']); ppt_show('P5 -> pending', $s, $ids);
wp_update_post(['ID' => $ids['P3'], 'post_status' => 'publish']); ppt_show('P3 -> publish', $s, $ids);
wp_update_post(['ID' => $ids['P5'], 'post_status' => 'publish']); ppt_show('P5 -> publish', $s, $ids);
wp_update_post(['ID' => $ids['P2'], 'post_status' => 'private']); ppt_show('P2 -> private', $s, $ids);
wp_update_post(['ID' => $ids['P4'], 'post_status' => 'draft']); ppt_show('P4 -> draft (P2 private)', $s, $ids);
wp_update_post(['ID' => $ids['P2'], 'post_status' => 'publish']); ppt_show('P2 private -> publish', $s, $ids);
wp_update_post(['ID' => $ids['P4'], 'post_status' => 'publish']); ppt_show('P4 -> publish', $s, $ids);
ppt_expect('S2 final', $s, $ids, ['P1','P2','P3','P4','P5']);

echo "S3 draft -> publish (new draft, and draft with a chosen part)\n";
$s = ppt_series('S3'); $ids = [];
for ($i = 1; $i <= 3; $i++) { $ids["P$i"] = ppt_post("S3 P$i", $s, 'publish', $i); }
$ids['D1'] = ppt_post('S3 D1 (no part)', $s, 'draft', 10);
$ids['D2'] = ppt_post('S3 D2 (part 2 chosen)', $s, 'draft', 11, 2);
ppt_show('3 published + 2 drafts', $s, $ids);
wp_update_post(['ID' => $ids['D1'], 'post_status' => 'publish']); ppt_show('publish D1', $s, $ids);
wp_update_post(['ID' => $ids['D2'], 'post_status' => 'publish']); ppt_show('publish D2 (wants part 2)', $s, $ids);
ppt_expect('S3 final', $s, $ids, ['P1','D2','P2','P3','D1']);

echo "S4 scheduled -> publish\n";
$s = ppt_series('S4'); $ids = [];
for ($i = 1; $i <= 3; $i++) { $ids["P$i"] = ppt_post("S4 P$i", $s, 'publish', $i); }
$ids['F1'] = ppt_post('S4 F1 scheduled', $s, 'future');
ppt_show('3 published + 1 scheduled', $s, $ids);
wp_publish_post($ids['F1']); ppt_show('cron publishes F1', $s, $ids);

echo "S5 permanent delete\n";
$s = ppt_series('S5'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S5 P$i", $s, 'publish', $i); }
wp_delete_post($ids['P2'], true); ppt_show('force-delete published P2', $s, $ids);
wp_trash_post($ids['P4']); ppt_show('trash P4', $s, $ids);
wp_delete_post($ids['P4'], true); ppt_show('empty trash (delete P4)', $s, $ids);

echo "S6 post in two series\n";
$a = ppt_series('S6A'); $b = ppt_series('S6B'); $ia = []; $ib = [];
for ($i = 1; $i <= 3; $i++) { $ia["A$i"] = ppt_post("S6 A$i", $a, 'publish', $i); }
for ($i = 1; $i <= 2; $i++) { $ib["B$i"] = ppt_post("S6 B$i", $b, 'publish', 3 + $i); }
$x = ppt_post('S6 X (both)', [$a, $b], 'publish', 1);
$ia['X'] = $x; $ib['X'] = $x;
// move X to slot 1 in A, keep last in B
set_series_order($a, $x, 1, true);
ppt_show('A: X moved to part 1', $a, $ia); ppt_show('B', $b, $ib);
wp_trash_post($x); ppt_show('A after trash X', $a, $ia); ppt_show('B after trash X', $b, $ib);
wp_untrash_post($x); wp_update_post(['ID' => $x, 'post_status' => 'publish']);
ppt_show('A after restore+publish X', $a, $ia); ppt_show('B after restore+publish X', $b, $ib);
ppt_expect('S6 A final', $a, $ia, ['X','A1','A2','A3']); ppt_expect('S6 B final', $b, $ib, ['B1','B2','X']);

echo "S7 restore straight to publish, others changed meanwhile\n";
$s = ppt_series('S7'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S7 P$i", $s, 'publish', $i); }
wp_trash_post($ids['P2']); wp_trash_post($ids['P4']); ppt_show('trash P2, P4', $s, $ids);
add_filter('wp_untrash_post_status', function ($st, $id, $prev) { return $prev; }, 10, 3);
wp_untrash_post($ids['P4']); ppt_show('restore P4 -> publish', $s, $ids);
wp_untrash_post($ids['P2']); ppt_show('restore P2 -> publish', $s, $ids);

ppt_expect('S7 final', $s, $ids, ['P1','P2','P3','P4','P5']);

echo "S8 adjacent posts leave, return in either order\n";
foreach ([['P3', 'P2'], ['P2', 'P3']] as $back) {
    $s = ppt_series('S8'); $ids = [];
    for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S8 P$i", $s, 'publish', $i); }
    wp_update_post(['ID' => $ids['P2'], 'post_status' => 'draft']); wp_update_post(['ID' => $ids['P3'], 'post_status' => 'draft']);
    ppt_show('P2, P3 -> draft', $s, $ids);
    foreach ($back as $n) { wp_update_post(['ID' => $ids[$n], 'post_status' => 'publish']); ppt_show("$n -> publish", $s, $ids); }
    ppt_expect('S8 final (' . implode(' then ', $back) . ')', $s, $ids, ['P1','P2','P3','P4','P5']);
}

echo "S9 editor publish with the Series metabox fields posted\n";
wp_set_current_user(1);
$s = ppt_series('S9'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S9 P$i", $s, 'publish', $i); }
wp_update_post(['ID' => $ids['P2'], 'post_status' => 'draft']); wp_update_post(['ID' => $ids['P4'], 'post_status' => 'draft']);
ppt_show('P2, P4 -> draft', $s, $ids);
foreach (['P4', 'P2'] as $n) {
    $shown = get_post_meta($ids[$n], SERIES_PART_KEY . '_' . $s, true); // what the metabox shows
    $_POST = $_REQUEST = ['post_series' => [$s], 'series_part' => [$s => $shown], 'is_series_save' => 1];
    wp_update_post(['ID' => $ids[$n], 'post_status' => 'publish']);
    $_POST = $_REQUEST = [];
    ppt_show("$n published from editor (box showed '" . $shown . "')", $s, $ids);
}
ppt_expect('S9 final', $s, $ids, ['P1','P2','P3','P4','P5']);
$_POST = $_REQUEST = ['post_series' => [$s], 'series_part' => [$s => '2'], 'is_series_save' => 1];
wp_update_post(['ID' => $ids['P5'], 'post_title' => 'S9 P5 moved']); $_POST = $_REQUEST = [];
ppt_show('editor: set P5 part to 2', $s, $ids);
ppt_expect('S9 after move', $s, $ids, ['P1','P5','P2','P3','P4']);

echo "S10 Manage Series: Update Order\n";
$s = ppt_series('S10'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S10 P$i", $s, 'publish', $i); }
series_issue_manager_part($s, implode(',', [$ids['P5'], $ids['P3'], $ids['P1'], $ids['P4'], $ids['P2']]));
ppt_show('dragged P5,P3,P1,P4,P2', $s, $ids);
ppt_expect('S10 final', $s, $ids, ['P5','P3','P1','P4','P2']);

echo "S11 unpublish from the classic editor / Pro Series box (box posts the part it loaded)\n";
wp_set_current_user(1);
$s = ppt_series('S11'); $ids = [];
for ($i = 1; $i <= 5; $i++) { $ids["P$i"] = ppt_post("S11 P$i", $s, 'publish', $i); }
$_POST = $_REQUEST = ['post_series' => [$s], 'series_part' => [$s => '3'], 'is_series_save' => 1];
wp_update_post(['ID' => $ids['P3'], 'post_status' => 'draft']); $_POST = $_REQUEST = [];
ppt_show('P3 -> draft via editor (box sent 3)', $s, $ids);
$kept = get_post_meta($ids['P3'], SERIES_PART_KEY . '_' . $s, true) === '' && is_array(get_post_meta($ids['P3'], SERIES_PART_KEY . '_' . $s . '_return', true));
if (!$kept) { $GLOBALS['pp_fail']++; }
printf("  %-34s %s\n", 'P3 part cleared, place remembered', $kept ? 'OK' : 'FAIL');
wp_trash_post($ids['P2']); ppt_show('trash P2', $s, $ids);
wp_update_post(['ID' => $ids['P3'], 'post_status' => 'publish']); ppt_show('publish P3', $s, $ids);
ppt_expect('S11 final', $s, $ids, ['P1','P3','P4','P5']);
$_POST = $_REQUEST = ['post_series' => [$s], 'series_part' => [$s => '3'], 'is_series_save' => 1];
wp_update_post(['ID' => $ids['P4'], 'post_status' => 'draft']); $_POST = $_REQUEST = [];
$_POST = $_REQUEST = ['post_series' => [$s], 'series_part' => [$s => '1'], 'is_series_save' => 1];
wp_update_post(['ID' => $ids['P4'], 'post_title' => 'S11 P4 wants part 1']); $_POST = $_REQUEST = [];
wp_update_post(['ID' => $ids['P4'], 'post_status' => 'publish']); ppt_show('P4 drafted, part 1 typed, published', $s, $ids);
ppt_expect('S11 explicit draft part', $s, $ids, ['P4','P1','P3','P5']);

echo "S12 more than 20 posts before the one that left\n";
$s = ppt_series('S12'); $ids = [];
for ($i = 1; $i <= 23; $i++) { $ids["P$i"] = ppt_post("S12 P$i", $s, 'publish', $i); }
wp_update_post(['ID' => $ids['P22'], 'post_status' => 'draft']);
for ($i = 2; $i <= 21; $i++) { wp_delete_post($ids["P$i"], true); unset($ids["P$i"]); }
wp_update_post(['ID' => $ids['P22'], 'post_status' => 'publish']); ppt_show('P22 back after P2-P21 deleted', $s, $ids);
ppt_expect('S12 final', $s, $ids, ['P1','P22','P23']);

echo "S13 series term_id differs from its term_taxonomy_id\n";
global $wpdb;
$next = (int) $wpdb->get_var("SELECT MAX(term_taxonomy_id) FROM {$wpdb->term_taxonomy}") + 50;
$wpdb->query("ALTER TABLE {$wpdb->term_taxonomy} AUTO_INCREMENT = $next");
$decoy = wp_insert_term('S13 decoy ' . wp_generate_password(4, false), 'category');
$wpdb->query("ALTER TABLE {$wpdb->terms} AUTO_INCREMENT = " . (int) $decoy['term_taxonomy_id']);
$s = ppt_series('S13');
$s_tt = (int) get_term($s, ppseries_get_series_slug())->term_taxonomy_id;
printf("  %-34s series term_id=%d tt_id=%d, decoy category tt_id=%d\n", 'ids', $s, $s_tt, $decoy['term_taxonomy_id']);
$d1 = wp_insert_post(['post_title' => 'S13 decoy public', 'post_status' => 'publish', 'post_category' => [$decoy['term_id']]]);
$d2 = wp_insert_post(['post_title' => 'S13 decoy private', 'post_status' => 'private', 'post_category' => [$decoy['term_id']]]);
$ids = [];
for ($i = 1; $i <= 3; $i++) { $ids["P$i"] = ppt_post("S13 P$i", $s, 'publish', $i); }
wp_update_post(['ID' => $ids['P2'], 'post_status' => 'draft']); ppt_show('P2 -> draft', $s, $ids);
clean_term_cache($decoy['term_id'], 'category');
$dc = (int) get_term($decoy['term_id'], 'category')->count;
if ($dc !== 1) { $GLOBALS['pp_fail']++; }
printf("  %-34s %d %s\n", 'decoy category count (core: 1)', $dc, $dc === 1 ? 'OK' : 'FAIL (unrelated term recounted)');

echo "\nFAILED CHECKS: {$GLOBALS['pp_fail']}\n";
