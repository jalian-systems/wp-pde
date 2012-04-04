For adding an external file to the plugin, open the `External Files` box from the
`Screen Options`. Enter a folder name (relative to the plugin directory) in the
`Folder` text field. Use the `Browse` button to select a file from the local system.
Click on `Add to Plugin`. PDE adds the file to the plugin.

Please note that PDE makes a copy of the file and saves it in the wordpress installation.
The changes made to the file using PDE's editor, do not reflect in the original file.

If you want to replace an external file in the plugin, delete the file from the PDE
project and add it again using the above instructions.

#### Including the files

When you upload either a javascript, php or a css file - you can let PDE include them
when the plugin is loaded. The `External Files` box, displays all the uploaded files
and provides options to `require` (for PHP files) and `enqueue` them for the javascript
and css files.
