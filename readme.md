# WpPDE - Plugin Development Environment for WordPress

* __Contributors__: kdmurthy
* __Donate link__: http://wp-pde.jaliansystems.com
* __Tags__: widgets, pde, development environment, actions, hooks, types, menu pages, metaboxes
* __Requires at least__: 3.3
* __Tested up to__: 3.3.1
* __Stable tag__: 0.9

A development environment for creating plugins with support for widgets, actions and hooks.

## Description

WpPDE is a development environment for WordPress. You can create your own plugins and maintain
them using WpPDE.

WpPDE allows you to create plugins that contain:

* Widgets
* Actions
* Hooks

WpPDE integrates with [Ace editor](http://ace.ajax.org) to provide a productive development
environment.

WpPDE also facilitate easy creation of readme files for your plugins.

## Documentation

* [WpPDE - Plugin Development Environment](http://wp-pde.jaliansystems.com/) - Official home page
* [WpPDE - User Guide](http://wp-pde.jaliansystems.com/documentation/) - Documentation for WpPDE plugin
* [WpPDE - Tutorials](http://wp-pde.jaliansystems.com/tutorials/) - Selected screencasts demonstrating the functionality.

### Plugin Elements

Use the __Plugin Elements__ box to add different type of items to your plugin. You can add:

* Actions
* Filters
* Widgets (More on this in the next section)

### Widgets

Once a widget element is added to the plugin, you can configure it in the GUI. You can also add
form elements to the widget. WpPDE automatically creates the widget form for you including saving of the widget
data. You just need to provide the display code (corresponding to `widget()` method).

### Form Elements

You can various kinds of form elements to the widget. These include:

* Labels (with various styles)
* Checkbox
* Text
* Text Area
* Radio
* Dropdown lists

You can also hide/unhide dependent elements using the value of the selected item for checkbox, radio and dropdown
items.

### External Files

You can add external files (javascript, css, PHP etc.) to your plugin using the `External Files` box. WpPDE even lets
you enqueue(or require) the files automatically.

### Readme Generation

WpPDE can generate readme files (in standard wordpress readme format or markdown format for github). Provide the
information in `About the Plugin` box and let WpPDE generate the files for you.

### Editor Options

You can change the editor options using the `Editor Options` box.

## Installation

1. Upload `wp-pde` to the plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions

[WpPDE Website](http://wp-pde.jaliansystems.com/f-a-q "F.A.Q")

## Screenshots

### WpPDE screen

<center>
<div><img src="http://wp-pde.jaliansystems.com/wp-content/uploads/2012/04/screenshot-1.png" width="800px;"/></div>
</center>

### Defining widget parameters.

<center>
<div><img src="http://wp-pde.jaliansystems.com/wp-content/uploads/2012/04/screenshot-2.png" /></div>
</center>

### A widget with form items added.

<center>
<div><img src="http://wp-pde.jaliansystems.com/wp-content/uploads/2012/04/screenshot-3.png" /></div>
</center>

### The editor

<center>
<div><img src="http://wp-pde.jaliansystems.com/wp-content/uploads/2012/04/screenshot-4.png" /></div>
</center>

## Changelog

### 1.0

* When a PDE Plugin is enabled/disabled, the page is refreshed. Any errors in the enabled plugin are displayed in the message area.
* Using the external files metabox, you can add a new empty file now.
* Text areas support full-width option.
* An action button component is available. WpPDE Pro menu pages add an action hook for the same. In widgets and metaboxes you need to use markup component to add appropriate ajax code and use wp_ajax/wp_ajax_nopriv hooks on the backend.
* A raw option is added for markup. The markup contents as is are copied into the generated code.
* Dropdown list supports multiple selection now.
* Dropdown list supports a php code fragment in the options. Use this to call a php method that generates the appropriate markup for the option list.
* A pde-widget-defaults hook is added. You can update the defaults for the widget for using this.
* Ace editor support is enhanced: you can select vim/emacs key binding. display indent option is also added. A editor keybindings help is added as a metabox. We use ace editor bundle from acebuilds project.
* Checkbox supports a label now.
* WpPDE supports FirePHP which is bundled along with it. You can use firephp classes/methods. See the help.
* Ace editor hooks to Command/Control+B for updating the project.
* The action/filter selection uses select2 dropdown which allows for search in the list. You need to populate the hooks table. See help.
* Save on change option for text area editor - useful with itsalltext plugin.
* Multi file uploader

Bug Fixes:

* The php-markdown folder is added to generated plugins. Needed to support help.
* Added License: to readme.txt
* When textarea editor is choosen the plugin duplicate functionality was not active.
* Editor change contents (text area) not setting dirty flag.
* The form editor supports upto 10 levels now. Due to a bug, this was only 4 levels earlier.
* Textarea component was not saving the number of rows.
* External files copied into the plugin folder have a '/' prefixed.
* The save editor contents, add form item etc. ajax calls shows proper error messages when failed now.
* A markup form item when added also creates a form field. Useful for adding hidden fields.
* Multiple color pickers not working in a single form.
* The zip contents do not have a version tag prefix now. Followed normal Wordpress custom.

### 0.9.4

* Added plugin selection for exporting a plugin project.
* BugFix: shortcode (<?php) in pde-form-walker.php
* Added delay_for_export() for post/page/metabox creation for making export work properly.
* Markup is structured better.
* Updated default styles for widget.
* Full screen editor support (use Command/Control+Enter to switch the editor mode)

### 0.9.3

* Compatibility changes for Wordpress 3.4 beta 2

### 0.9.2

#### Features:

* Updated help files.
* Added support for date and color pickers

#### Bugs Squashed:

* Binary file uploads works properly now.
* If a binary file is selected for edit, an error message is shown.
* Duplicate files are not allowed for uploading.
* Unneeded message in duplicate project.
* Changed references to marathontesting.com to wp-pde.jaliansystems.com
* Updated/corrected links to WpPDE site in online help.
* Fixed issue with description_html_escape being wrongly set to display_when variable.
* Widget Parameters: removed description_html_escape field. Not used in for this field.
* Widget#update should not be using default values.
* When giving options for radio and dropdown, the options can be enclosed in '"' so that a ',' can be escaped if required.
* Fixed display of colon at the end of an empty title.
* Added return statement to 'filter' plugin item.
* Delinked message-area from the static messages when the plugin is refreshed.
* Fixed: radio button css
* Escaping quotes in titles, descriptions.
* About metabox - used esc_attr at some places.

### 0.9

* First release

### Upgrade Notice

# 0.9

* First release

