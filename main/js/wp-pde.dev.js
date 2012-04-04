/**
 * WordPress Administration PDE Plugin
 * Interface JS functions
 *
 * @version 2.0.0
 *
 * @package WordPress
 * @subpackage Administration
 */

var wpPDEPlugin;

(function($) {

	var api = wpPDEPlugin = {

		options : {
			formItemDepthPerLevel : 30, // Do not use directly. Use depthToPx and pxToDepth instead.
			globalMaxDepth : 11
		},

    messageClean: true,
    aceEditor: undefined,
		formList : undefined,	// Set in init.
		targetList : undefined, // Set in init.
    editorChanged: false,
    contentChanged: false,
		isRTL: !! ( 'undefined' != typeof isRtl && isRtl ),
		negateIfRTL: ( 'undefined' != typeof isRtl && isRtl ) ? -1 : 1,

		// Functions that run on init.
		init : function() {
			this.jQueryExtensions();

			this.attachPluginEditListeners();

			this.setupInputWithDefaultTitle();

			this.attachTabsPanelListeners();

			this.attachUnsavedChangesListener();

      this.attachPluginContentListener();

      this.attachEditorListeners() ;

      this.attachEditorSelectionListeners() ;

      this.attachActionHookListeners();

      this.attachFormItemListeners();

			this.initSortables();

			this.initToggles();

			this.initTabManager();

		},

    attachPluginContentListener: function() {
      $('#plugin-name, #plugin-version').unbind( 'change' );
      $('#plugin-name, #plugin-version').bind( 'change', function(e) {
        api.registerContentChange(true);
      });
    },

    attachFormItemListeners: function() {
      $('#form-item-param-type').change(function (e) {
        display = $(e.target).val() == 'text' || $(e.target).val() == 'textarea' || $(e.target).val() == 'password' ;
        $('#form-item-html-option-wrap').css('display', display ? 'block' : 'none')
      });
    },

    attachEditorSelectionListeners: function() {
      $('#editor-option').change(function (e) {
        $('#ace-editor-options').css('display', $(e.target).val() == 'Ace' ? 'block' : 'none')
      });
    },

    setAceEditor : function(ace_mode) {
      if ($('#editorcontent').size() == 0)
        return;
			height = $('#templateside').height();
			if (height > 720)
				$('#editorcontent').height(height);
			else
				$('#editorcontent').height(720);

      if (api.aceEditor != undefined) {
        api.aceEditor.getSession().removeAllListeners();
      }
      api.aceEditor = ace.edit("editorcontent");
      api.aceEditor.setTheme("ace/theme/" + wpPDEPluginVar.ace_theme);
      var PHPMode = ace.require("ace/mode/" + ace_mode).Mode;
      api.aceEditor.getSession().setMode(new PHPMode());
      api.aceEditor.setShowPrintMargin(wpPDEPluginVar.ace_print_margin == 'Yes');
      api.aceEditor.getSession().setUseWrapMode(wpPDEPluginVar.ace_wrap_mode == 'Yes');
      if (wpPDEPluginVar.ace_display_gutter == 'No')
        $('div.ace_gutter').css('display', 'none');
      $('#editorcontent').css('font-size', wpPDEPluginVar.ace_font_size);
			readonly = $('#editor-mode').val() == 'readonly';
			api.aceEditor.setReadOnly(readonly);
			if (readonly)
				$('#save-file').attr('disabled', 'disabled');
      var command = {
          name: "save-file",
          bindKey: {
              mac: "Command-S",
              win: "Ctrl-S"
          },
          called: false,
          exec: function(editor) { $('#save-file').click(); }
      };

      api.aceEditor.commands.addCommand(command);
      this.attachTextAreaChangeListeners();

    },

    attachFormAreaChangeListeners: function() {
      $('.plugin input, .plugin select').unbind( 'change' );
      $('.plugin textarea, .plugin input, .plugin select').bind( 'change', function(e) {
          api.registerEditorChange(true);
      });
    },

    attachTextAreaChangeListeners : function() {
      if (api.aceEditor != undefined) {
        api.aceEditor.getSession().on('change', function (e) {
          api.registerEditorChange(true);
        });
      } else {
        $('#editorcontent').change(function (e) {
          api.registerEditorChange(true);
        });
      }
			$('#update-pde-plugin').unbind('submit');
      $('#update-pde-plugin').bind( 'submit', function (e) {
        if (api.editorChanged) {
          if (!confirm(wpPDEPluginVar.warnLoseEditorContents)) {
            e.preventDefault();
            return false;
          }
        }
        action = $('#update-pde-plugin input[name="action"]').val();
        if($('#old-plugin-name').size() > 0 && action != 'duplicate' ) {
          old_name = $('#old-plugin-name').val();
          old_version = $('#old-plugin-version').val();
          if(old_name != $('#plugin-name').val() || old_version != $('#plugin-version').val()) {
            if (confirm(wpPDEPluginVar.duplicateProject)) {
              $('#update-pde-plugin input[name="action"]').val('duplicate');
              e.preventDefault();
              $('#update-pde-plugin').submit();
            }
          }
        }
        return true;
      });
    },

    registerEditorChange : function(b) {
      api.editorChanged = b;
    },

    registerContentChange : function(b) {
      api.contentChanged = b;
    },

    attachActionHookListeners : function () {
       $('#pdeplugin-item-type').change (function (e) {
          var type = $('#pdeplugin-item-type').val();
          $('#pdepluginitemdiv .pdeplugin-item-optional').css('display', 'none');
          $('#pdepluginitemdiv .enable-for-' + type).css('display', 'block');
       });

       $('#submit-pdepluginitemdiv').click (function (e) {
          e.preventDefault();
          var name = $('#pdeplugin-item-name').val(),
              method = $('#pdeplugin-item-method').val(),
              priority = $('#pdeplugin-item-priority').val(),
              args = $('#pdeplugin-item-args').val(),
              type = $('#pdeplugin-item-type').val();

          if (name === 'Name' || name === '' || ((type == 'action' || type == 'filter') && (method === 'Method Name' || method === ''
                || priority.replace(/\d+/,'') || args.replace(/\d+/, '')))) {
              alert("Invalid values...");
              return;
          }
          var wpnonce = $('#add-pdeplugin-item-nonce').val();
          params = {
            'action': 'add-pdeplugin-item',
            'plugin_id': $('#plugin').val(),
            'pluginitem_name': name,
            'pluginitem_type': type,
            'item_args': $('#add-pdeplugin-items input, #add-pdeplugin-items select').serialize(),
            'add-pdeplugin-item-nonce': wpnonce,
          };

          $('.pdepluginitemdiv img.waiting').show();
          $.post( ajaxurl, params, function(response, sstatus) {
            $('.pdepluginitemdiv img.waiting').hide();
            $('#message-area').html(response['message']);
            if (response['error'] != 'error') {
              $('li.edit-file-link').removeClass('highlight') ;
              $('#editor-' + type + '-list').css('display', 'block');
              $('#editor-' + type + '-list ul').html(response['data']);
            }
            $('#pdeplugin-item-name').val('').blur();
            $('#pdeplugin-item-method').val('').blur();
            $('#pdeplugin-item-priority').val('10');
            $('#pdeplugin-item-args').val('1');
            wpPDEPlugin.attachEditorListeners();
            api.messageClean = false ;
            if (response['error'] != 'error')
              $('#templateside li.highlight a.edit-file-link').click();
            api.messageClean = true ;
          }, 'json');
        });
    },

    setEditorContents: function(data, mode, ace_mode) {
      $('#editor-area').css('display', 'block');
      if (api.aceEditor == undefined)
        $('#editortemplate #editorcontent').val(data);
      else {
        api.setAceEditor(ace_mode);
        api.aceEditor.getSession().setValue(data);
        api.registerEditorChange(false);
				api.aceEditor.setReadOnly(mode == 'readonly');
				if (mode == 'readonly')
					$('#save-file').attr('disabled', 'disabled');
				else
					$('#save-file').removeAttr('disabled');
      }
    },

    getEditorContents: function() {
      if (api.aceEditor == undefined)
        return $('#editorcontent').val();
      else {
        return api.aceEditor.getSession().getValue();
      }
    },

    attachEditorListeners : function () {
			$('.update-file-contents img.waiting').hide();
      $('#save-file').unbind ('click');
      $('#save-file').bind ('click', function (e) {
        e.preventDefault();
        api.saveFileContents();
      });

      $('li.edit-file-link').unbind ('click');
      $('li.edit-file-link').bind ('click', function (e) {
        if (e.target == this) {
          $(this).children('a.edit-file-link').click();
        }
        return true ;
      });

      $('li.edit-file-link a.edit-file-link').unbind ('click');
      $('li.edit-file-link a.edit-file-link').bind ('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (api.editorChanged) {
          if (!confirm(wpPDEPluginVar.warnLoseEditorContents)) {
            return;
          }
        }
        api.registerEditorChange(false);
        prevHighlight = $('li.edit-file-link.highlight');

        $('li.edit-file-link').removeClass('highlight') ;
        $(this).parent('li').addClass("highlight");
        $('.update-file-contents img.waiting').show();
        file_id = $(this).attr('href').match(/file_id=([0-9]+)/)[1];
        $.get($(this).attr('href'), function(response) {
          $('.update-file-contents img.waiting').hide();
          if (response['error'] != 'error') {
            if (response['mime-type'] == 'text') {
              api.setEditorContents(response['data'], response['mode'], response['ace_mode']);
					    $('#editor-area').css('display', 'block');
					    $('#post-body-content').css('display', 'none');
              $('#editor-current-file').val(response['file_id']);
              $('#save-file-contents-nonce').val(response['save_nonce']);
              $('#submit-addformitemdiv').attr('disabled', 'disabled');
            } else if (response['mime-type'] == 'form-markup') {
						  $('#save-file').removeAttr('disabled');
              $('#post-body-content').html(response['data']);
					    $('#editor-area').css('display', 'none');
					    $('#post-body-content').css('display', 'block');
              $('#editor-current-file').val(response['file_id']);
              $('#save-file-contents-nonce').val(response['save_nonce']);
              if(response['can_add_items'])
                $('#submit-addformitemdiv').removeAttr('disabled');
              else
                $('#submit-addformitemdiv').attr('disabled', 'disabled');
              wpPDEPlugin.initSortables();
              wpPDEPlugin.attachFormAreaChangeListeners();
            }
          } else {
            $('li.edit-file-link').removeClass('highlight') ;
            prevHighlight.addClass('highlight');
          }
          if (api.messageClean)
            $('#message-area').html(response['message']);
          else
            $('#message-area').html($('#message-area').html() + response['message']);
        }, 'json');
      });

      $('li.edit-file-link a.delete-file-link').unbind ('click');
      $('li.edit-file-link a.delete-file-link').bind ('click', function (e) {
        e.stopPropagation();
        currentHighlight = $('li.edit-file-link.highlight');
        $('li.edit-file-link.highlight').removeClass('highlight') ;
        $(this).parent('li').addClass("highlight");
        if (!confirm(wpPDEPluginVar.warnDeleteItem)) {
          $('li.edit-file-link.highlight').removeClass('highlight') ;
          currentHighlight.addClass("highlight");
          e.preventDefault();
          return false;
        }
        return true;
      });
    },

    saveFileContents : function() {
			// Update plugin item position data
			api.formList.find('.form-item-data-position').val( function(index) { return index + 1; } );

      globalFail = false ;
      $('.edit-form-item-title').each (function(index) {
          itemName = $(this).val();
			    if( !itemName || !itemName.replace(/\s+/, '') || itemName == '__') {
            if (!globalFail)
              alert( wpPDEPluginVar.errorIllegalValues );
            globalFail = true ;
            $(this).addClass('form-invalid');
          }
      });
      if (globalFail) {
        globalFail = false ;
        return false ;
      }

      var newContent = api.getEditorContents(),
          wpnonce = $('#save-file-contents-nonce').val(),
          file_id = $('#editor-current-file').val(),
          source = $('#editor-area').css('display') == 'block',
          form_data = $('#update-pde-plugin').serialize();

			params = {
				'action': 'save-file-contents',
				'file_id': file_id,
				'save-file-contents-nonce': wpnonce,
        'form_data': form_data,
        'source': source,
        'newcontent': newContent,
			};

			$('.update-file-contents img.waiting').show();
			$.post( ajaxurl, params, function(response) {
        api.registerEditorChange(false);
			  $('.update-file-contents img.waiting').hide();
        $('#message-area').html(response['message']);
      }, 'json');
    },

		jQueryExtensions : function() {
			// jQuery extensions
			$.fn.extend({
				formItemDepth : function() {
					var margin = api.isRTL ? this.eq(0).css('margin-right') : this.eq(0).css('margin-left');
					return api.pxToDepth( margin && -1 != margin.indexOf('px') ? margin.slice(0, -2) : 0 );
				},
				updateDepthClass : function(current, prev) {
					return this.each(function(){
						var t = $(this);
						prev = prev || t.formItemDepth();
						$(this).removeClass('form-item-depth-'+ prev )
							.addClass('form-item-depth-'+ current );
					});
				},
				shiftDepthClass : function(change) {
					return this.each(function(){
						var t = $(this),
							depth = t.formItemDepth();
						$(this).removeClass('form-item-depth-'+ depth )
							.addClass('form-item-depth-'+ (depth + change) );
					});
				},
				childFormItems : function() {
					var result = $();
					this.each(function(){
						var t = $(this), depth = t.formItemDepth(), next = t.next();
						while( next.length && next.formItemDepth() > depth ) {
							result = result.add( next );
							next = next.next();
						}
					});
					return result;
				},
				updateParentFormItemDBId : function() {
					return this.each(function(){
						var item = $(this),
							input = item.find('.form-item-data-parent-id'),
							depth = item.formItemDepth(),
							parent = item.prev();

						if( depth == 0 ) { // Item is on the top level, has no parent
							input.val(0);
						} else { // Find the parent item, and retrieve its object id.
							while( ! parent[0] || ! parent[0].className || -1 == parent[0].className.indexOf('form-item') || ( parent.formItemDepth() != depth - 1 ) )
								parent = parent.prev();
							input.val( parent.find('.form-item-data-db-id').val() );
						}
					});
				},
				hideAdvancedFormItemFields : function() {
					return this.each(function(){
						var that = $(this);
						$('.hide-column-tog').not(':checked').each(function(){
							that.find('.field-' + $(this).val() ).addClass('hidden-field');
						});
					});
				},
				getItemData : function( itemType, id ) {
					itemType = itemType || 'form-item';

					var itemData = {};

					if( !id && itemType == 'form-item' ) {
						id = this.find('.form-item-data-db-id').val();
					}

					if( !id ) return itemData;

					this.find('input').each(function() {
				    var match = this.name.match(/db-(\d+)\[([^\]]*)\]/);
            if (match) {
              itemData[match[2]] = this.value;
            } else {
              console.log(this.name, ' did not match');
            }
					});

					return itemData;
				},
				setItemData : function( itemData, itemType, id ) { // Can take a type, such as 'form-item', or an id.
					itemType = itemType || 'form-item';

					if( !id && itemType == 'form-item' ) {
						id = $('.form-item-data-db-id', this).val();
					}

					if( !id ) return this;

					this.find('input').each(function() {
						var t = $(this), field;
				    var match = this.name.match(/db-(\d+)\[([^\]]*)\]/);
            if (match) {
              t.val(itemData[match[2]]);
            } else {
              console.log(this.name, ' did not match');
            }
					});
					return this;
				}
			});
		},

		initToggles : function() {
			// init postboxes
			postboxes.add_postbox_toggles('toplevel_page_wp_pde');

			// adjust columns functions for plugins UI
			columns.useCheckboxesForHidden();
			columns.checked = function(field) {
				$('.field-' + field).removeClass('hidden-field');
			}
			columns.unchecked = function(field) {
				$('.field-' + field).addClass('hidden-field');
			}
			// hide fields
			api.formList.hideAdvancedFormItemFields();
		},

		initSortables : function() {
			api.formList = $('#form-to-edit');
			api.targetList = api.formList;

			if( !api.formList.length )
        return ;

			var currentDepth = 0, originalDepth, minDepth, maxDepth,
				prev, next, prevBottom, nextThreshold, helperHeight, transport,
				formEdge = api.formList.offset().left,
				body = $('body'), maxChildDepth,
				formMaxDepth = initialPluginMaxDepth();

			// Use the right edge if RTL.
			formEdge += api.isRTL ? api.formList.width() : 0;

			api.formList.sortable({
				handle: '.form-item-handle',
				placeholder: 'sortable-placeholder',
				start: function(e, ui) {
					var height, width, parent, children, tempHolder;

					// handle placement for rtl orientation
					if ( api.isRTL )
						ui.item[0].style.right = 'auto';

					transport = ui.item.children('.form-item-transport');

					// Set depths. currentDepth must be set before children are located.
					originalDepth = ui.item.formItemDepth();
					updateCurrentDepth(ui, originalDepth);

					// Attach child elements to parent
					// Skip the placeholder
					parent = ( ui.item.next()[0] == ui.placeholder[0] ) ? ui.item.next() : ui.item;
					children = parent.childFormItems();
					transport.append( children );

					// Update the height of the placeholder to match the moving item.
					height = transport.outerHeight();
					// If there are children, account for distance between top of children and parent
					height += ( height > 0 ) ? (ui.placeholder.css('margin-top').slice(0, -2) * 1) : 0;
					height += ui.helper.outerHeight();
					helperHeight = height;
					height -= 2; // Subtract 2 for borders
					ui.placeholder.height(height);

					// Update the width of the placeholder to match the moving item.
					maxChildDepth = originalDepth;
					children.each(function(){
						var depth = $(this).formItemDepth();
						maxChildDepth = (depth > maxChildDepth) ? depth : maxChildDepth;
					});
					width = ui.helper.find('.form-item-handle').outerWidth(); // Get original width
					width += api.depthToPx(maxChildDepth - originalDepth); // Account for children
					width -= 2; // Subtract 2 for borders
					ui.placeholder.width(width);

					// Update the list of plugin items.
					tempHolder = ui.placeholder.next();
					tempHolder.css( 'margin-top', helperHeight + 'px' ); // Set the margin to absorb the placeholder
					ui.placeholder.detach(); // detach or jQuery UI will think the placeholder is a plugin item
					$(this).sortable( "refresh" ); // The children aren't sortable. We should let jQ UI know.
					ui.item.after( ui.placeholder ); // reattach the placeholder.
					tempHolder.css('margin-top', 0); // reset the margin

					// Now that the element is complete, we can update...
					updateSharedVars(ui);
				},
				stop: function(e, ui) {
					var children, depthChange = currentDepth - originalDepth;

					// Return child elements to the list
					children = transport.children().insertAfter(ui.item);

					// Update depth classes
					if( depthChange != 0 ) {
						ui.item.updateDepthClass( currentDepth );
						children.shiftDepthClass( depthChange );
						updatePluginMaxDepth( depthChange );
					}
					// Register a change
					api.registerEditorChange(true);
					// Update the item data.
					ui.item.updateParentFormItemDBId();

					// address sortable's incorrectly-calculated top in opera
					ui.item[0].style.top = 0;

					// handle drop placement for rtl orientation
					if ( api.isRTL ) {
						ui.item[0].style.left = 'auto';
						ui.item[0].style.right = 0;
					}

					// The width of the tab bar might have changed. Just in case.
					api.refreshPluginTabs( true );
				},
				change: function(e, ui) {
					// Make sure the placeholder is inside the plugin.
					// Otherwise fix it, or we're in trouble.
					if( ! ui.placeholder.parent().hasClass('plugin') )
						(prev.length) ? prev.after( ui.placeholder ) : api.formList.prepend( ui.placeholder );

					updateSharedVars(ui);
				},
				sort: function(e, ui) {
					var offset = ui.helper.offset(),
						edge = api.isRTL ? offset.left + ui.helper.width() : offset.left,
						depth = api.negateIfRTL * api.pxToDepth( edge - formEdge );
					// Check and correct if depth is not within range.
					// Also, if the dragged element is dragged upwards over
					// an item, shift the placeholder to a child position.
					if ( depth > maxDepth || offset.top < prevBottom ) depth = maxDepth;
					else if ( depth < minDepth ) depth = minDepth;

					if( depth != currentDepth )
						updateCurrentDepth(ui, depth);

					// If we overlap the next element, manually shift downwards
					if( nextThreshold && offset.top + helperHeight > nextThreshold ) {
						next.after( ui.placeholder );
						updateSharedVars( ui );
						$(this).sortable( "refreshPositions" );
					}
				}
			});

			function updateSharedVars(ui) {
				var depth;

				prev = ui.placeholder.prev();
				next = ui.placeholder.next();

				// Make sure we don't select the moving item.
				if( prev[0] == ui.item[0] ) prev = prev.prev();
				if( next[0] == ui.item[0] ) next = next.next();

				prevBottom = (prev.length) ? prev.offset().top + prev.height() : 0;
				nextThreshold = (next.length) ? next.offset().top + next.height() / 3 : 0;
				minDepth = (next.length) ? next.formItemDepth() : 0;

				if( prev.length )
					maxDepth = ( (depth = prev.formItemDepth() + 1) > api.options.globalMaxDepth ) ? api.options.globalMaxDepth : depth;
				else
					maxDepth = 0;
			}

			function updateCurrentDepth(ui, depth) {
				ui.placeholder.updateDepthClass( depth, currentDepth );
				currentDepth = depth;
			}

			function initialPluginMaxDepth() {
				if( ! body[0].className ) return 0;
				var match = body[0].className.match(/form-max-depth-(\d+)/);
				return match && match[1] ? parseInt(match[1]) : 0;
			}

			function updatePluginMaxDepth( depthChange ) {
				var depth, newDepth = formMaxDepth;
				if ( depthChange === 0 ) {
					return;
				} else if ( depthChange > 0 ) {
					depth = maxChildDepth + depthChange;
					if( depth > formMaxDepth )
						newDepth = depth;
				} else if ( depthChange < 0 && maxChildDepth == formMaxDepth ) {
					while( ! $('.form-item-depth-' + newDepth, api.formList).length && newDepth > 0 )
						newDepth--;
				}
				// Update the depth class.
				body.removeClass( 'form-max-depth-' + formMaxDepth ).addClass( 'form-max-depth-' + newDepth );
				formMaxDepth = newDepth;
			}
		},

		attachPluginEditListeners : function() {
			var that = this;
			$('#update-pde-plugin').unbind('click');
			$('#update-pde-plugin').bind('click', function(e) {
				if ( e.target && e.target.className ) {
					if ( -1 != e.target.className.indexOf('item-edit') ) {
						return that.eventOnClickEditLink(e.target);
					} else if ( -1 != e.target.className.indexOf('plugin-save') ) {
						return that.eventOnClickPluginSave(e.target);
					} else if ( -1 != e.target.className.indexOf('plugin-delete') ) {
						return that.eventOnClickPluginDelete(e.target);
					} else if ( -1 != e.target.className.indexOf('item-delete') ) {
						return that.eventOnClickFormItemDelete(e.target);
					} else if ( -1 != e.target.className.indexOf('item-cancel') ) {
						return that.eventOnClickCancelLink(e.target);
					}
				}
			});

			$('#add-form-items input[type="text"]').keypress(function(e){
				if ( e.keyCode === 13 ) {
					e.preventDefault();
					$("#submit-addformitemdiv").click();
				}
			});

			$('#add-pdeplugin-items input[type="text"]').keypress(function(e){
				if ( e.keyCode === 13 ) {
					e.preventDefault();
					$("#submit-pdepluginitemdiv").click();
				}
			});
		},

		/**
		 * An interface for managing default values for input elements
		 * that is both JS and accessibility-friendly.
		 *
		 * Input elements that add the class 'input-with-default-title'
		 * will have their values set to the provided HTML title when empty.
		 */
		setupInputWithDefaultTitle : function() {
			var name = 'input-with-default-title';

			$('.' + name).each( function(){
				var $t = $(this), title = $t.attr('title'), val = $t.val();
				$t.data( name, title );

				if( '' == val ) $t.val( title );
				else if ( title == val ) return;
				else $t.removeClass( name );
			}).focus( function(){
				var $t = $(this);
				if( $t.val() == $t.data(name) )
					$t.val('').removeClass( name );
			}).blur( function(){
				var $t = $(this);
				if( '' == $t.val() )
					$t.addClass( name ).val( $t.data(name) );
			});
		},

		addCustomLink : function( ) {
			var param_type = $('#form-item-param-type').val(),
				label = $('#form-item-name').val(),
        html_option = $('#form-item-html-option').val();

			if ( '' == label || 'Display Label' == label )
        return false ;

			processMethod = api.addFormItemToBottom;

			// Show the ajax spinner
			$('.addformitemdiv img.waiting').show();
			this.addItemToForm( param_type, label, html_option, processMethod, function() {
				// Remove the ajax spinner
				$('.addformitemdiv img.waiting').hide();
				// Set custom link form back to defaults
				$('#form-item-name').val('').blur();
				$('#form-item-param-type').val('Text');
				$('#form-item-html-option').val('No');
			});
		},

		addItemToForm : function(param_type, label, html_option, processMethod, callback) {
			var plugin = $('#plugin').val(),
				form_id  = $('#editor-current-file').val(),
				nonce = $('#add_form_item_nonce').val();

			processMethod = processMethod || function(){};
			callback = callback || function(){};

      position = 0;
			last_item = $('#form-to-edit .form-item-data-position').filter(':last');
      if (last_item.size() == 1) {
        position = parseInt(last_item.val()) + 1;
      }

			params = {
				'action': 'add-form-item',
				'plugin_id': plugin,
				'add_form_item_nonce': nonce,
        'param_type': param_type,
        'title': label,
        'form_id': form_id,
        'html_option': html_option,
        'position': position,
			};

			$.post( ajaxurl, params, function(response) {
        $('#message-area').html(response['message']);
        if (response['error'] != 'error') {
          $('#message-area').html(response['message']);
				  var ins = $('#plugin-instructions');
				  processMethod(response['data'], params);
				  if( ! ins.hasClass('plugin-instructions-inactive') && ins.siblings().length )
					  ins.addClass('plugin-instructions-inactive');
        }
				callback();
			}, 'json');
		},

		/**
		 * Process the add form item request response into plugin list item.
		 *
		 * @param string formMarkup The text server response of plugin item markup.
		 * @param object req The request arguments.
		 */
		addFormItemToBottom : function( formMarkup, req ) {
			$(formMarkup).hideAdvancedFormItemFields().appendTo( api.targetList );
		},

		addFormItemToTop : function( formMarkup, req ) {
			$(formMarkup).hideAdvancedFormItemFields().prependTo( api.targetList );
		},

		attachUnsavedChangesListener : function() {
			if ( $('#plugin').val() != 0 ) {
				window.onbeforeunload = function(){
					if ( api.editorChanged || api.contentChanged )
						return wpPDEPluginVar.saveAlert;
				};
			} else {
				$('#addformitemdiv, #pdepluginitemdiv').find('input,select').prop('disabled', true).end().find('a').attr('href', '#').unbind('click');
			}
		},

		attachTabsPanelListeners : function() {
			$('#addformitemdiv, #pdepluginitemdiv').bind('click', function(e) {
				var target = $(e.target);

				if ( e.target.id && 'submit-addformitemdiv' == e.target.id )
						api.addCustomLink( );
        return false ;
			});
		},

		initTabManager : function() {
			var fixed = $('.wp-pde-tabs-wrapper'),
				fluid = fixed.children('.wp-pde-tabs'),
				active = fluid.children('.wp-pde-tab-active'),
				tabs = fluid.children('.wp-pde-tab'),
				tabsWidth = 0,
				fixedRight, fixedLeft,
				arrowLeft, arrowRight, resizeTimer, css = {},
				marginFluid = api.isRTL ? 'margin-right' : 'margin-left',
				marginFixed = api.isRTL ? 'margin-left' : 'margin-right',
				msPerPx = 2;

			/**
			 * Refreshes the plugin tabs.
			 * Will show and hide arrows where necessary.
			 * Scrolls to the active tab by default.
			 *
			 * @param savePosition {boolean} Optional. Prevents scrolling so
			 * 		  that the current position is maintained. Default false.
			 **/
			api.refreshPluginTabs = function( savePosition ) {
				var fixedWidth = fixed.width(),
					margin = 0, css = {};
				fixedLeft = fixed.offset().left;
				fixedRight = fixedLeft + fixedWidth;

				if( !savePosition )
					active.makeTabVisible();

				// Prevent space from building up next to the last tab if there's more to show
				if( tabs.last().isTabVisible() ) {
					margin = fixed.width() - tabsWidth;
					margin = margin > 0 ? 0 : margin;
					css[marginFluid] = margin + 'px';
					fluid.animate( css, 100, "linear" );
				}

				// Show the arrows only when necessary
				if( fixedWidth > tabsWidth )
					arrowLeft.add( arrowRight ).hide();
				else
					arrowLeft.add( arrowRight ).show();
			}

			$.fn.extend({
				makeTabVisible : function() {
					var t = this.eq(0), left, right, css = {}, shift = 0;

					if( ! t.length ) return this;

					left = t.offset().left;
					right = left + t.outerWidth();

					if( right > fixedRight )
						shift = fixedRight - right;
					else if ( left < fixedLeft )
						shift = fixedLeft - left;

					if( ! shift ) return this;

					css[marginFluid] = "+=" + api.negateIfRTL * shift + 'px';
					fluid.animate( css, Math.abs( shift ) * msPerPx, "linear" );
					return this;
				},
				isTabVisible : function() {
					var t = this.eq(0),
						left = t.offset().left,
						right = left + t.outerWidth();
					return ( right <= fixedRight && left >= fixedLeft ) ? true : false;
				}
			});

			// Find the width of all tabs
			tabs.each(function(){
				tabsWidth += $(this).outerWidth(true);
			});

			// Set up fixed margin for overflow, unset padding
			css['padding'] = 0;
			css[marginFixed] = (-1 * tabsWidth) + 'px';
			fluid.css( css );

			arrowLeft = $('<div class="wp-pde-tabs-arrow wp-pde-tabs-arrow-left"><a>&laquo;</a></div>');
			arrowRight = $('<div class="wp-pde-tabs-arrow wp-pde-tabs-arrow-right"><a>&raquo;</a></div>');
			// Attach to the document
			fixed.wrap('<div class="wp-pde-tabs-navigation"/>').parent().prepend( arrowLeft ).append( arrowRight );

			// Set the plugin tabs
			api.refreshPluginTabs();
			// Make sure the tabs reset on resize
			$(window).resize(function() {
				if( resizeTimer ) clearTimeout(resizeTimer);
				resizeTimer = setTimeout( api.refreshPluginTabs, 200);
			});

			// Build arrow functions
			$.each([{
					arrow : arrowLeft,
					next : "next",
					last : "first",
					operator : "+="
				},{
					arrow : arrowRight,
					next : "prev",
					last : "last",
					operator : "-="
				}], function(){
				var that = this;
				this.arrow.mousedown(function(){
					var marginFluidVal = Math.abs( parseInt( fluid.css(marginFluid) ) ),
						shift = marginFluidVal,
						css = {};

					if( "-=" == that.operator )
						shift = Math.abs( tabsWidth - fixed.width() ) - marginFluidVal;

					if( ! shift ) return;

					css[marginFluid] = that.operator + shift + 'px';
					fluid.animate( css, shift * msPerPx, "linear" );
				}).mouseup(function(){
					var tab, next;
					fluid.stop(true);
					tab = tabs[that.last]();
					while( (next = tab[that.next]()) && next.length && ! next.isTabVisible() ) {
						tab = next;
					}
					tab.makeTabVisible();
				});
			});
		},

    eventOnClickEditLink : function(clickedEl) {
      var settings, item ;

      match = clickedEl.id.match(/form-item-edit-([0-9]+)/);
      if (!match) {
        console.log('could not find form-item-edit-999 in ' + clickedEl.id);
        return false;
      }
      settings = $('#form-item-settings-' + match[1]);
      item = settings.parent();
      if( 0 != item.length ) {
        if( item.hasClass('form-item-edit-inactive') ) {
          if( ! settings.data('form-item-data') ) {
            settings.data( 'form-item-data', settings.getItemData() );
          }
          height = settings.height();
          // A fix for slideDown not working properly. Use a custom animation.
          //settings.slideDown('fast');
          settings.show().animate({height: height}, {duration: 200});
          item.removeClass('form-item-edit-inactive')
            .addClass('form-item-edit-active');
        } else {
          settings.slideUp('fast');
          item.removeClass('form-item-edit-active')
            .addClass('form-item-edit-inactive');
        }
        return false;
      }
    },

		eventOnClickCancelLink : function(clickedEl) {
			var settings = $(clickedEl).closest('.form-item-settings');
			settings.setItemData( settings.data('form-item-data') );
			return false;
		},

		eventOnClickPluginSave : function(clickedEl) {
			pluginName = $('#plugin-name'),
			pluginNameVal = pluginName.val();
			// Cancel and warn if invalid plugin name
			if( !pluginNameVal || pluginNameVal == pluginName.attr('title') || !pluginNameVal.replace(/\s+/, '') ) {
				pluginName.parent().addClass('form-invalid');
				return false;
			}
			window.onbeforeunload = null;

			return true;
		},

		eventOnClickPluginDelete : function(clickedEl) {
			// Delete warning AYS
			if ( confirm( wpPDEPluginVar.warnDeletePlugin ) ) {
				window.onbeforeunload = null;
				return true;
			}
			return false;
		},

		eventOnClickFormItemDelete : function(clickedEl) {
			var itemID = parseInt(clickedEl.id.replace('delete-', ''), 10);
			api.removeFormItem( $('#form-item-' + itemID) , clickedEl.href);
			return false;
		},

		removeFormItem : function(el, href) {
			var children = el.childFormItems();

      $.get(href, function(response) {
        if (response['error'] != 'error') {
          el.addClass('deleting').animate({
              opacity : 0,
              height: 0
            }, 350, function() {
              var ins = $('#plugin-instructions');
              el.remove();
              children.shiftDepthClass(-1).updateParentFormItemDBId();
              if( ! ins.siblings().length )
                ins.removeClass('plugin-instructions-inactive');
            });
        }
        $('#message-area').html(response['message']);
      }, 'json');
		},

		depthToPx : function(depth) {
			return depth * api.options.formItemDepthPerLevel;
		},

		pxToDepth : function(px) {
			return Math.floor(px / api.options.formItemDepthPerLevel);
		}

	};

	$(document).ready(function(){
    if (wpPDEPluginVar.editor == 'Ace') {
      api.setAceEditor('php');
    }
    $('#editorcontent').css('display', 'block');
    wpPDEPlugin.init();
    $('#templateside li.highlight a.edit-file-link').click();
  });

})(jQuery);
