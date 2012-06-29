<?php
/**
 * WordPress Administration for PDE Plugins
 * Interface functions
 *
 * @version 2.0.0
 *
 * @package WordPress
 * @subpackage Administration
 */
// Permissions Check
if ( ! is_super_admin() )
	wp_die( __( 'Only administrators are allowed.' ) );

// jQuery
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'jquery-ui-droppable' );
wp_enqueue_script( 'jquery-ui-sortable' );

// Metaboxes
wp_enqueue_script( 'common' );
wp_enqueue_script( 'wp-lists' );
wp_enqueue_script( 'postbox' );

if ( get_magic_quotes_gpc() ) {
  $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
}

// Container for any messages displayed to the user
$messages = array();

// The plugin id of the current plugin being edited
global $pde_plugin_selected_id;
$pde_plugin_selected_id = isset( $_REQUEST['plugin'] ) ? (int) $_REQUEST['plugin'] : 0;

// Allowed actions: add, update, delete
$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'edit';

switch ( $action ) {
  case 'update-options':
		global $current_user;
		check_admin_referer( 'update-options-' . $current_user->user_login);
		update_user_meta( $current_user->ID, 'wp_pde_editor', $_REQUEST['editor-option']);
		update_user_meta( $current_user->ID, 'wp_pde_ace_theme', $_REQUEST['ace-editor-theme']);
		update_user_meta( $current_user->ID, 'wp_pde_ace_display_gutter', $_REQUEST['ace-editor-display-gutter']);
		update_user_meta( $current_user->ID, 'wp_pde_ace_font_size', $_REQUEST['ace-editor-font-size']);
		update_user_meta( $current_user->ID, 'wp_pde_ace_print_margin', $_REQUEST['ace-editor-print-margin']);
		update_user_meta( $current_user->ID, 'wp_pde_ace_wrap_mode', $_REQUEST['ace-editor-wrap-mode']);
    break ;

  case 'add-file':
		check_admin_referer( 'add-pdeplugin-file-' . $pde_plugin_selected_id );
    $_plugin_object = PDEPlugin::get( $pde_plugin_selected_id );
    if( !is_wp_error( $_plugin_object ) ) {
      foreach( $_FILES as $key => $file_entry ) {
        if( $file_entry['error'] ) {
        } else {
          $path = empty( $_REQUEST['file_path'] ) ? '' : $_REQUEST['file_path'] ;
          $filename = $path . '/' . basename($file_entry['name']) ;
          $item = $_plugin_object->create_external_file($filename, file_get_contents( $file_entry['tmp_name'] ) );
          if( is_wp_error( $item ) )
            WpPDEPlugin::messages('error', $item->get_error_message(), $messages);
        }
      }
      $files = $_plugin_object->get_external_file_items();
      foreach( $files as $file ) {
        if( isset( $_REQUEST['db-' . $file->db_id] ) )
          $file->update( $_REQUEST['db-' . $file->db_id], $messages );
      }
    } else {
      WpPDEPlugin::messages('error', $_plugin_object->get_error_message(), $messages);
      unset($_plugin_object);
    }

    break;
    
  case 'delete-file':
		check_admin_referer( 'delete-file-' . $_REQUEST['file_id']);
    $item = PDEPluginItem::get( $_REQUEST['file_id'] );
    if( !is_wp_error( $item ) )
      $r = $item->delete_source($messages, $_REQUEST['plugin_id']);
    break ;

  case 'update-meta':
    check_admin_referer( 'update-meta-' . $pde_plugin_selected_id);
    $_plugin_object = PDEPlugin::get($pde_plugin_selected_id);
    $_plugin_object->update_meta($_REQUEST);
    break;

	case 'delete':
		check_admin_referer( 'delete-pde_plugin-' . $pde_plugin_selected_id );

		if ( PDEPlugin::isa( $pde_plugin_selected_id ) ) {
			$deleted_pde_plugin = PDEPlugin::get( $pde_plugin_selected_id );

      $r = $deleted_pde_plugin->delete($messages);
			if ( is_wp_error($r) ) {
        WpPDEPlugin::messages('error', $r->get_error_message(), $messages);
			} else {
        WpPDEPlugin::messages('updated fade', __('The plugin has been successfully deleted.'), $messages);
				// Select the next available plugin
				$pde_plugin_selected_id = 0;
				$_pde_plugins = PDEPlugin::get_all();
				foreach( $_pde_plugins as $index => $_pde_plugin ) {
					if ( strcmp( $_pde_plugin->plugin_name, $deleted_pde_plugin->plugin_name ) >= 0
					 || $index == count( $_pde_plugins ) - 1 ) {
						$pde_plugin_selected_id = $_pde_plugin->plugin_id;
            $_plugin_object = $_pde_plugin;
						break;
					}
				}
			}
			unset( $delete_pde_plugin, $deleted_pde_plugin, $_pde_plugins );
		} else {
			// Reset the selected plugin
			$pde_plugin_selected_id = 0;
			unset( $_REQUEST['plugin'] );
		}
		break;

  case 'duplicate':
		check_admin_referer( 'update-pde_plugin', 'update-pde-plugin-nonce' );
    $new_plugin_title = trim( esc_html( $_REQUEST['plugin-name'] ) );
    $plugin_version = trim( esc_html ( $_REQUEST['plugin-version'] ) );

    if ( $new_plugin_title ) {
      $_plugin_object = PDEPlugin::duplicate( $pde_plugin_selected_id, array('plugin-name' => $new_plugin_title, 'plugin-version' => $plugin_version) , $messages);
      if ( is_wp_error( $_plugin_object ) ) {
        WpPDEPlugin::messages('error', $_plugin_object->get_error_message(), $messages);
        unset( $_plugin_object );
      } else {
        $pde_plugin_selected_id = $_plugin_object->plugin_id ;
        WpPDEPlugin::messages('updated fade', sprintf( __('The <strong>%s</strong> plugin has been successfully duplicated.'), $_plugin_object->plugin_name ), $messages);
      }
    } else {
      WpPDEPlugin::messages('error', __('Please enter a valid plugin name.'), $messages);
    }

    break;

	case 'update':

		check_admin_referer( 'update-pde_plugin', 'update-pde-plugin-nonce' );
    $project_dir = PDEPlugin::get_projects_dir();
    if (isset($_REQUEST['test_plugin'])) {
      if (! is_writable ( $project_dir )) {
        WpPDEPlugin::messages('error', sprintf( __('You can enable plugins for testing only when the projects folder (<strong>%s</strong>) is writable.'), $project_dir), $messages);
      } else {
        $_plugin_object = PDEPlugin::get( $pde_plugin_selected_id );
        $plugin_test = ! $_plugin_object->get_option('test');
        $_plugin_object->update_option( 'test', $plugin_test);
        WpPDEPlugin::messages('updated fade', sprintf(__('The plugin has been %s.'), $plugin_test ? 'enabled' : 'disabled'), $messages);
      }
    } else if( isset( $_REQUEST['export_plugin'] ) )  {
        WpPDEPlugin::messages('error', __( 'Not yet implemented'), $messages);
    } else {
      // Add Plugin
      if ( 0 == $pde_plugin_selected_id ) {
        $new_plugin_title = trim( esc_html( $_REQUEST['plugin-name'] ) );
        $plugin_version = trim( esc_html ( $_REQUEST['plugin-version'] ) );

        if ( $new_plugin_title ) {
          $_plugin_object = PDEPlugin::create(array('plugin-name' => $new_plugin_title, 'plugin-version' => $plugin_version) , $messages);
          if ( is_wp_error( $_plugin_object ) ) {
            WpPDEPlugin::messages('error', $_plugin_object->get_error_message(), $messages);
            unset( $_plugin_object );
          } else {
            $pde_plugin_selected_id = $_plugin_object->plugin_id ;
            WpPDEPlugin::messages('updated fade', sprintf( __('The <strong>%s</strong> plugin has been successfully created.'), $_plugin_object->plugin_name ), $messages);
          }
        } else {
          WpPDEPlugin::messages('error', __('Please enter a valid plugin name.'), $messages);
        }

      // update existing plugin
      } else {
        $_plugin_object = PDEPlugin::get( $pde_plugin_selected_id );

        $plugin_title = trim( esc_html( $_REQUEST['plugin-name'] ) );
        $plugin_version = trim( esc_html ( $_REQUEST['plugin-version'] ) );
        if ( ! $plugin_title ) {
          WpPDEPlugin::messages('error', __('Please enter a valid plugin name.'), $messages);
          $plugin_title = $_plugin_object->plugin_name;
        } else {
            if ( ! is_wp_error( $_plugin_object ) ) {
              $_plugin_object->delete_project($messages);
              $_plugin_object = $_plugin_object->update( array( 'plugin-name' => $plugin_title, 'plugin-version' => $plugin_version ) );
              if ( is_wp_error( $_plugin_object ) ) {
                WpPDEPlugin::messages('error', $_plugin_object->get_error_message(), $messages);
                unset( $_plugin_object );
              }
            }
        }
      }

      if (0 != $pde_plugin_selected_id) {
          if (! is_writable ( $project_dir )) {
            WpPDEPlugin::messages('error', sprintf( __('You could test your plugins if the projects folder (<strong>%s</strong>) is writable.'), $project_dir), $messages);
          } else
            $_plugin_object->create_project($messages);
        }
      }
		break;
}

PDEPlugin::setup_editor();

// Get all pde plugins
$pde_plugins = PDEPlugin::get_all();

// Get recently edited pde plugin
$recently_edited = (int) get_user_option( 'pde_plugin_recently_edited' );

// If there was no recently edited plugin, and $pde_plugin_selected_id is a pde plugin, update recently edited plugin.
if ( !$recently_edited && PDEPlugin::isa( $pde_plugin_selected_id ) ) {
	$recently_edited = $pde_plugin_selected_id;

// Else if $pde_plugin_selected_id is not a plugin and not requesting that we create a new plugin, but $recently_edited is a plugin, grab that one.
} elseif ( 0 == $pde_plugin_selected_id && ! isset( $_REQUEST['plugin'] ) && PDEPlugin::isa( $recently_edited ) ) {
	$pde_plugin_selected_id = $recently_edited;

// Else try to grab the first plugin from the plugins list
} elseif ( 0 == $pde_plugin_selected_id && ! isset( $_REQUEST['plugin'] ) && ! empty($pde_plugins) ) {
	$pde_plugin_selected_id = $pde_plugins[0]->plugin_id;
}

// Update the user's setting
global $current_user;
if ( $pde_plugin_selected_id != $recently_edited && PDEPlugin::isa( $pde_plugin_selected_id ) )
	update_user_meta( $current_user->ID, 'pde_plugin_recently_edited', $pde_plugin_selected_id );

// If there's a plugin, get its name.
if ( ! isset($_plugin_object) && PDEPlugin::isa( $pde_plugin_selected_id ) ) {
	$_plugin_object = PDEPlugin::get( $pde_plugin_selected_id );
}

global $editor_current_file ;
$form_markup = false ;
$editor_content = '';
$editor_mode = 'readwrite' ;

global $_ww_pde_plugin_max_depth;
$_ww_pde_plugin_max_depth = 0;

// Setup the editor content
if (isset($_plugin_object)) {
	if (!isset($_REQUEST['editor-current-file']) || empty($_REQUEST['editor-current-file'])) {
    $recently_edited_file = get_user_option ( 'pde_file_recently_edited-' . $pde_plugin_selected_id ) ;
    if ( !$recently_edited_file  || is_wp_error( PDEPluginItem::get( $recently_edited_file ) ))
		  $editor_current_file = $_plugin_object->get_available_file();
    else
      $editor_current_file = $recently_edited_file ;
  }
  else
    $editor_current_file = $_REQUEST['editor-current-file'];

  if ($editor_current_file) {
    if ( isset( $recently_edited_file ) && $recently_edited_file != $editor_current_file ) {
      update_user_meta ( $current_user->ID, 'pde_file_recently_edited-' . $pde_plugin_selected_id, $editor_current_file );
    }

    $item = PDEPluginItem::get ( $editor_current_file );
    if( !is_wp_error( $item ) ) {
      if( PDEPluginItem::is_form ( $item ) ) {
        $form_markup = true ;
        $edit_markup = $item->get_edit_markup() ;
      }
      else {
        $editor_content = $item->get_source( $messages );
				if ( PDEPluginItem::is_generated_file( $item ) )
					$editor_mode = 'readonly' ;
			}
    }
  }
}

// Generate truncated plugin names
foreach( (array) $pde_plugins as $key => $_pde_plugin ) {
	$_pde_plugin->truncated_name = trim( wp_html_excerpt( $_pde_plugin->plugin_name, 40 ) );
	if ( $_pde_plugin->truncated_name != $_pde_plugin->plugin_name )
		$_pde_plugin->truncated_name .= '&hellip;';

	$pde_plugins[$key]->truncated_name = $_pde_plugin->truncated_name . ' v' . $_pde_plugin->plugin_version;
}


if (isset($_plugin_object)) {
  $plugin_name = $_plugin_object->plugin_name ;
  $plugin_version = $_plugin_object->plugin_version;
} else {
  $plugin_name = '';
  $plugin_version = '0.1';
}

?>
<div class="wrap">
  <img src="<?php echo plugins_url('images/wppdelogo.png', __FILE__); ?>" height="62" width="166"/>

  <?php foreach ($messages as $message ) echo $message ; ?>

  <div id="message-area">
  </div>
	<div id="pde-plugins-frame">
	<div id="plugin-settings-column" class="metabox-holder<?php if ( !$pde_plugin_selected_id ) { echo ' metabox-holder-disabled'; } ?>">

      <?php PDEPlugin::go_pro(); ?>
			<?php do_meta_boxes( null, 'side', array ('plugin' => isset ( $_plugin_object ) ? $_plugin_object : null, 'file_id' => $editor_current_file) ); ?>

	</div><!-- /#plugin-settings-column -->
	<div id="plugin-management-liquid">
		<div id="plugin-management">
			<div class="wp-pde-tabs-wrapper">
			<div class="wp-pde-tabs">
				<?php
				foreach( (array) $pde_plugins as $_pde_plugin ) :
					if ( $pde_plugin_selected_id == $_pde_plugin->plugin_id ) : ?><span class="wp-pde-tab wp-pde-tab-active">
							<?php echo esc_html( $_pde_plugin->truncated_name ); ?>
						</span><?php else : ?><a href="<?php
							echo esc_url(add_query_arg(
								array(
									'action' => 'edit',
									'plugin' => $_pde_plugin->plugin_id,
								)
							));
						?>" class="wp-pde-tab hide-if-no-js">
							<?php echo esc_html( $_pde_plugin->truncated_name ); ?>
						</a><?php endif;
				endforeach;
				if ( 0 == $pde_plugin_selected_id ) : ?><span class="wp-pde-tab plugin-add-new wp-pde-tab-active">
					<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add plugin' ) ); ?>
				</span><?php else : ?><a href="<?php
					echo esc_url(add_query_arg(
						array(
							'action' => 'edit',
							'plugin' => 0,
						)
					));
				?>" class="wp-pde-tab plugin-add-new">
					<?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add plugin' ) ); ?>
				</a><?php endif; ?>
			</div>
			</div>
			<div class="plugin-edit">
				<form id="update-pde-plugin" action="#" method="post" enctype="multipart/form-data">
					<div id="pde-plugin-header">
						<div id="submitpost" class="submitbox">
							<div class="major-publishing-actions">
                <div class="input-elements">
                  <label id="plugin-name-label" class="plugin-name-label plugin-info-label" for="plugin-name">
                    <span><?php _e('Plugin Name'); ?></span>
                    <input name="plugin-name" id="plugin-name" type="text" class="plugin-name plugin-info-field" title="<?php esc_attr_e('Enter plugin name here'); ?>" value="<?php echo esc_attr( $plugin_name  ); ?>" />
                  </label>
                  <label id="plugin-version-label" class="plugin-version-label plugin-info-label" for="plugin-version">
                  <span><?php _e('Version'); ?></span>
                   <input name="plugin-version" id="plugin-version" type="text" class="plugin-version plugin-info-field" title="<?php esc_attr_e('Enter author name here'); ?>" value="<?php esc_attr_e( $plugin_version ); ?>" />
                  </label>
                </div> <!-- Input Elements -->
								<div class="publishing-action">
                  <?php if( isset( $_plugin_object) ) {
                          ?>
                          <input name="old-plugin-name" id="old-plugin-name" type="hidden" value="<?php echo esc_attr( $plugin_name  ); ?>" />
                          <input name="old-plugin-version" id="old-plugin-version" type="hidden" value="<?php esc_attr_e( $plugin_version ); ?>" />
                          <?php
                          $_plugin_object->add_export_button('export_plugin');
                          $_plugin_object->add_test_button('test_plugin_header');
                        }
                  ?>
									<?php submit_button( empty( $pde_plugin_selected_id ) ? __( 'Create Plugin' ) : __( 'Save Plugin' ), 'button-primary plugin-save', 'save_plugin', false, array( 'id' => 'save_plugin_header' ) ); ?>
								</div><!-- END .publishing-action -->

								<?php if ( ! empty( $pde_plugin_selected_id ) ) : ?>
								<div class="delete-action">
									<a class="submitdelete deletion plugin-delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array('action' => 'delete', 'plugin' => $pde_plugin_selected_id)), 'delete-pde_plugin-' . $pde_plugin_selected_id ) ); ?>"><?php _e('Delete Plugin'); ?></a>
								</div><!-- END .delete-action -->
								<?php endif; ?>
							</div><!-- END .major-publishing-actions -->
						</div><!-- END #submitpost .submitbox -->
						<?php
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
						wp_nonce_field( 'update-pde_plugin', 'update-pde-plugin-nonce' );
						?>
            <input type="hidden" name="editor-current-file" id="editor-current-file" value="<?php echo $editor_current_file; ?>" /><!-- set by the JS also when a file is opened -->
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="plugin" id="plugin" value="<?php echo esc_attr( $pde_plugin_selected_id ); ?>" />
					</div><!-- END #pde-plugin-header -->
					<div id="post-body">

            <div id="templateside">
            <?php if ( isset ($_plugin_object) ): ?>

                <?php
                  $file_menu = array('widget' => __('Widgets'),
                                     'action' => __('Actions'),
                                     'filter' => __('Filters'),
                                     'external_file' => __('External Files'), );

                  $custom_plugin_items = apply_filters('pde_custom_plugin_items', array());
                  foreach( $custom_plugin_items as $plugin_item ) {
                     $file_menu[$plugin_item['value']] = $plugin_item['display_plural'];
                  }
                  
                  $file_menu['generated_source'] = __('Generated Sources') ;

                  foreach(  $file_menu as $d => $disp ) {
                    $r = call_user_func( array(&$_plugin_object, '_emit_editor_actions' ), $d, $editor_current_file );
                    $cls='';
                    if( in_array($d, get_hidden_columns(get_current_screen()) ) )
                      $cls='hidden-field';
                    $style = $r ? '' : 'style="display:none;"' ;
                    echo "<div class='field-{$d} $cls'><div id='editor-{$d}-list' $style>\n";
                    echo "<h4>" . $disp . "</h4>\n" ;
                    echo "<ul>$r</ul>\n";
                    echo "</div></div>\n";
                  }
                 ?>

            <?php endif ?>
            </div>

            <!--- Editor start -->
            <?php if ( isset ($_plugin_object) ): ?>
            <div id='editor-area' <?php if( $form_markup || !$editor_current_file) echo ' style="display:none"'; ?>>

            <div id="editortemplate">
                <?php wp_nonce_field( 'save-file-contents-' . $editor_current_file, 'save-file-contents-nonce' ); ?>
								<input type='hidden' id='editor-mode' value="<?php echo $editor_mode; ?>" name="editor_mode" />
                <div>
                  <?php $editor = get_user_option('wp_pde_editor'); if (!$editor) $editor = 'Ace'; ?>
                  <?php if ($editor == 'TextArea'): ?>
                    <textarea rows="25" name="editorcontent" id="editorcontent" style="display:none;"><?php echo esc_textarea($editor_content); ?></textarea>
                  <?php endif; ?>
                  <?php if ($editor == 'Ace'): ?>
                    <pre id="editorcontent" style="display:none;"><?php echo esc_textarea($editor_content); ?></pre>
                  <?php endif; ?>
                </div>
            </div>
            </div>
            <?php endif; ?>
            <!--- Editor end -->

						<div id="post-body-content" <?php if ( !$form_markup ) echo ' style="display:none"' ; ?>>
							<?php
							if ( isset( $edit_markup ) ) {
								if ( ! is_wp_error( $edit_markup ) )
									echo $edit_markup;
							} else if ( empty( $pde_plugin_selected_id ) ) {
								echo '<div class="post-body-plain">';
								echo '<p>' . __('To create a custom plugin, give it a name above and click Create Plugin. Then choose items like pages, categories or custom links from the left column to add to this plugin.') . '</p>';
								echo '<p>' . __('After you have added your items, drag and drop to put them in the order you want. You can also click each item to reveal additional configuration options.') . '</p>';
								echo '<p>' . __('When you have finished building your custom plugin, make sure you click the Save Plugin button.') . '</p>';
								echo '</div>';
							}
							?>
						</div><!-- /#post-body-content -->

					</div><!-- /#post-body -->

					<div id="pde-plugin-footer">
						<div class="major-publishing-actions">
						  <div class="publishing-action">
                <span class="update-file-contents">
                  <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                  <?php submit_button( __( 'Save' ), 'primary', 'save-file', false, array( 'tabindex' => '2' ) ); ?>
                </span>
						  </div>
						</div>
					</div><!-- /#pde-plugin-footer -->
				</form><!-- /#update-pde-plugin -->
			</div><!-- /.plugin-edit -->
		</div><!-- /#plugin-management -->

    <!-- Metabox normal -->
		<?php if ($pde_plugin_selected_id): ?>
      <div class="metabox-holder metabox-holder-normal">
			  <?php do_meta_boxes( null, 'normal', array ('plugin' => isset( $_plugin_object ) ? $_plugin_object : null, 'file_id' => $editor_current_file) ); ?>
      </div>
    <?php endif; ?>
    <!-- Metabox normal end -->

	</div><!-- /#plugin-management-liquid -->
	</div><!-- /#pde-plugins-frame -->
</div><!-- /.wrap-->
