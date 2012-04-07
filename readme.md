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

### 0.9.2

Features:
    * Updated help files.
    * Added support for date and color pickers

Bugs Squashed:
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

