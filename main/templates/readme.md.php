<?php
	if (!function_exists('println')) { function println($s) { echo str_replace("\r\n", "\n", $s); echo "\n"; } }
	$meta = $plugin->get_meta();
	extract($meta);
?>
# <?php echo $plugin->plugin_name; ?>

<?php echo str_replace('=', '##', $meta_short_description); ?>


## Description

<?php echo str_replace('=', '##', $meta_long_description); ?>

# Installation

<?php echo str_replace('=', '##', $meta_installation); ?>

# Changelog

<?php echo str_replace('=', '##', $meta_changelog); ?>

