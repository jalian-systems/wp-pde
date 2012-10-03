<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
?>
    $<?php echo $var; ?> = esc_attr( $instance['<?php echo $var; ?>'] );
@>
    <div class="pde-form-field pde-form-text <?php echo $var; ?>">
      <div class="pde-form-title">
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
        </label>
      </div>
      <div class="pde-form-input">
        <input type="text" value="<@php echo $<?php echo $var; ?>; ?>" name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>" class="pde-plugin-pickcolor-text"/>
        <a style="-moz-border-radius: 4px;-webkit-border-radius: 4px;border-radius: 4px;border: 1px solid #dfdfdf;margin: 0 7px 0 3px;padding: 4px 14px;display: inline;" href="#" class="pde-plugin-pickcolor-example hide-if-no-js" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-example"></a>
        <input id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-button" type="button" class="pde-plugin-pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select' ); ?>" />
        <div id="<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-colorPickerDiv" class="pde-plugin-pickcolor-popup"></div>
      </div>
<?php if( !empty( $description ) ): ?>
      <div class="pde-form-description">
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><?php echo $description; ?></span>
        </label>
      </div>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->
<script type="text/javascript">
(function($){
  var <?php echo $var; ?>_farbtastic = undefined;
	var <?php echo $var; ?>_pickColor = function(a) {
		$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(a);
		$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-example').css('background-color', a);
	};

  $('#wpbody-content').on( 'click', "#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-button, #<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-example", function(e) {
    e.preventDefault();
    id = $(e.target).attr('id').replace(/-button$|-example$/, '')
		if ( <?php echo $var; ?>_farbtastic == undefined ) {
      <?php echo $var; ?>_pickColor = function(a) {
        <?php echo $var; ?>_farbtastic.setColor(a);
        $('#' + id).val(a);
        $('#' + id + '-example').css('background-color', a);
      };
		  <?php echo $var; ?>_farbtastic = $.farbtastic('#' + id + '-colorPickerDiv', <?php echo $var; ?>_pickColor);
      $(document).mousedown( function() {
        $('#' + id + '-colorPickerDiv').hide();
      });
    }

    $('#' + id + '-colorPickerDiv').show();
  });

  $('#wpbody-content').on( 'keyup', '#<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>',  function() {
    var a = $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(),
      b = a;

    a = a.replace(/[^a-fA-F0-9]/, '');
    if ( '#' + a !== b )
      $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val(a);
    if ( a.length === 3 || a.length === 6 )
      <?php echo $var; ?>_pickColor( '#' + a );
  });

	$(document).ready( function() {

		<?php echo $var; ?>_pickColor( $('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>').val() );

		$(document).mousedown( function() {
			$('#<@php echo $this->get_field_id('<?php echo $var; ?>'); @>-colorPickerDiv').hide();
		});

	});
})(jQuery);
</script>
<@php 
