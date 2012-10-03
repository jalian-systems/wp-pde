You can use PDE to add actions and filters to the plugin.

For adding a action or a filter open the `Plugin Elements` meta box, if it is not already open,
using `Screen Options`. Select either `Action` or `Filter` from the `Type` dropdown.
Either select a name from the dropdown or select `Other` and provide a name for the action/filter
(like _wp\_head_, _wp\_excerpt_ etc.) in the `Action` or `Filter`
text field, give a name for the method and click on `Add to Plugin` option. PDE adds
the hook and opens the hook method in the editor.

PDE displays the actions and filters in the plugin in a list to the right of the editor area.
Click on the trash icon to delete an action or a filter from the plugin.

#### Populating the action/filter database ####

Each WordPress installation is different and provide different sets of hooks depending on the plugins
installed and configuration. So, PDE does not pre-populate the actions/filters in the combobox. You can
populate the hooks by visiting any admin/front-end page of your WordPress installation after adding a
query argument `instrument` with value `hooks` to it.

For example for collecting all the actions/filters for the login page visit:

http://<your-site-url>/wp-login.php?instrument=hooks

