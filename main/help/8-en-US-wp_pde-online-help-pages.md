<font color="red">(WpPDE Pro)</font>

You can provide online help to your metaboxes and plugins by creating help
files using markdown format and adding them as external files to the plugin.
The files should be named in the following format:

`sort-key`-`language`-`item-key`-`title-of-page`.md

Where:  
  sort-key is any combination of characters used to sort the files.  
  language is the `bloginfo('language')` - eg. en-US  
  item-key is the `meta-key` for metaboxes and `menu-slug` for menu pages  
  title-of-page is the title for this help page.  

An example file can be `8-en-US-wp_pde-online-help-pages.md`.

For metaboxes these files should be uploaded into `meta_boxes/help` folder and
for menu pages upload these files into `menu_pages/help` folder.

You can also set the help sidebar by uploading a file named in the following format:

`language`-`item-key`-sidebar.md

Please note that, where as the help pages are **added** to the existing help contents
of standard post pages (`posts`, `pages`), the sidebar contents are replaced. If your
metaboxes are targeted for standard post pages, avoid providing sidebar contents for
your metabox.

