<?php
	if (!function_exists('println')) { function println($s) { echo str_replace("\r\n", "\n", $s); echo "\n"; } }
	$meta = $plugin->get_meta();
	extract($meta);
?>
=== <?php echo $plugin->plugin_name; ?> ===
Contributors: <?php println($meta_contributors); ?>
Donate link: <?php println($meta_donate_link); ?>
Tags: <?php println($meta_tags); ?>
Requires at least: <?php println($meta_requires_at_least); ?>
Tested up to: <?php println($meta_tested_upto); ?>
Stable tag: <?php println($meta_stable_tag); ?>
License: <?php println($meta_license); ?>

<?php println($meta_short_description); ?>

== Description ==

<?php println($meta_long_description); ?>

== Installation ==

<?php println($meta_installation); ?>

== Frequently Asked Questions ==

<?php println($meta_faq); ?>

== Screenshots ==

<?php println($meta_screenshots); ?>

== Changelog ==

<?php println($meta_changelog); ?>

== Upgrade Notice ==

<?php println($meta_upgrade_notice); ?>

<?php println($meta_extra); ?>

