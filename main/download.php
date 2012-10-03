<?php

function Zip($source, $destination, $prefix='') {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if( !empty( $prefix ) ) {
      $zip->addEmptyDir($prefix . '/');
    }

    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            if( basename($file) == '.' || basename($file) == '..' )
              continue ;
            $file = str_replace('\\', '/', realpath($file));

            if (is_dir($file) === true) {
                $zip->addEmptyDir( $prefix . '/' . str_replace($source . '/', '', $file . '/'));
            } else if (is_file($file) === true) {
                $zip->addFromString( $prefix . '/' . str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    } else if (is_file($source) === true) {
        $zip->addFromString( $prefix . '/' . basename($source), file_get_contents($source));
    }

    return $zip->close();
}


$plugin = PDEPlugin::get( $_REQUEST['plugin_id'] );
$filename = $plugin->plugin_name;
$prefix = strtolower( sanitize_file_name( $filename ) );
$filename = $prefix . '.zip';

$xfiles = scandir( PDEPlugin::get_projects_dir() );
foreach( $xfiles as $xfile ) {
  if( strpos( $xfile, 'export' ) === 0 ) {
    if( is_dir( PDEPlugin::get_projects_dir() . '/' . $xfile ) )
      $plugin->rrmdir( PDEPlugin::get_projects_dir() . '/' . $xfile );
    else
      unlink( PDEPlugin::get_projects_dir() . '/' . $xfile );
  }
}

$project_dir = $plugin->get_project_dir('export');

$plugin->rrmdir( $project_dir );
$messages = array();
$plugin->create_project($messages, 'export');

$zipfile = tempnam( PDEPlugin::get_projects_dir(), 'export');

header("Content-Type: application/zip");
header('Content-Disposition: attachment; filename=' . $filename);
Zip($project_dir, $zipfile, $prefix);
readfile($zipfile);
exit;
?>
