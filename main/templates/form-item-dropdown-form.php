<?php
  $var = $item->get_php_variable();
  $description = $item->get_description() ;
  if( !empty( $description ) && !empty( $item->description_html_escape ) )
    $description = '<@php _e( \'' . esc_html( $item->get_description() ) . '\' ); @>' ;
  $select_text = $item->get_select_text();
  $none_val = sanitize_title_with_dashes($select_text);
  $options = $item->get_option_values();
?>
    $<?php echo $var; ?> = $instance['<?php echo $var; ?>'] ;
?>
    <div class="pde-form-field pde-form-dropdown<?php if( !empty( $item->select_multiple ) ) echo '-multiple'; ?> <?php echo $var; ?>">
      <div class="pde-form-title">
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><@php esc_html_e( __(<?php _pv( $item->get_title() ); ?>) ); @></span>
        </label>
      </div>
      <div class="pde-form-input">
<?php if( empty( $item->select_multiple ) ) : ?>
        <select name="<@php echo $this->get_field_name('<?php echo $var; ?>'); ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
<?php if(strpos($item->options, '<?php') === 0) {
        echo str_replace('<?php', '<@php', $item->options) ;
      } else {
        foreach( $options as $key => $value ) {
          $value = esc_attr($value);
          $esc_value = esc_html($value); ?>
            <option value="<?php echo $value; ?>"<@php selected( $instance['<?php echo $var; ?>'], <?php _pv( $value ); ?> ); ?>><@php _e('<?php echo $esc_value; ?>'); ?></option>
  <?php   }
      } ?>
        </select>

<?php else : ?>
      <@php
        $key = 0 ;
        if( !empty( $<?php echo $var; ?> ) )
          foreach( $<?php echo $var; ?> as $<?php echo $var; ?>_single ) {
            if ( $<?php echo $var; ?>_single != '<?php echo $none_val; ?>' ) {
              @><select name="<@php echo $this->get_field_name('<?php echo $var; ?>') . '[' . $key . ']'; ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>') . '_' . $key; ?>">
              <option value="<?php echo $none_val; ?>"><?php esc_html_e($select_text); ?></option>
      <?php if(strpos($item->options, '<?php') === 0) {
              echo str_replace('<?php', '<@php', $item->options) ;
            } else {
              foreach( $options as $key => $value ) {
                $value = esc_attr($value);
                $esc_value = esc_html($value); ?>
                  <option value="<?php echo $value; ?>"<@php selected( $<?php echo $var; ?>_single, <?php _pv( $value ); ?> ); ?>><@php _e('<?php echo $esc_value; ?>'); ?></option>
        <?php   }
            } ?>
              </select><@php

              $key++ ;
              echo "<br/>";
            }
          }
        $<?php echo $var; ?>_single = '<?php echo $none_val; ?>';
        echo '<div class="pde-form-item-dropdown-new-item">';
        @><select name="<@php echo $this->get_field_name('<?php echo $var; ?>') . '[' . $key . ']'; ?>" id="<@php echo $this->get_field_id('<?php echo $var; ?>') . '_' . $key; ?>">
              <option value="<?php echo $none_val; ?>"><?php esc_html_e($select_text); ?></option>
<?php if(strpos($item->options, '<?php') === 0) {
        echo str_replace('<?php', '<@php', $item->options) ;
      } else {
        foreach( $options as $key => $value ) {
          $value = esc_attr($value);
          $esc_value = esc_html($value); ?>
            <option value="<?php echo $value; ?>"<@php selected( $<?php echo $var; ?>_single, <?php _pv( $value ); ?> ); ?>><@php _e('<?php echo $esc_value; ?>'); ?></option>
  <?php   }
      } ?>
        </select><@php
        echo '</div>';
        $key++ ;
@>

<?php endif; ?>
      </div>

<?php if( !empty( $description ) ): ?>
      <div class="pde-form-description">
        <label for="<@php echo $this->get_field_id('<?php echo $var; ?>'); ?>">
          <span><?php echo $description; ?></span>
        </label>
      </div>
<?php endif; ?>
    </div> <!-- <?php echo $var; ?> -->
<?php if( !empty( $item->select_multiple ) ) : ?>
<script type="text/javascript">
(function($){
  $('#wpbody-content').off( 'change', '.pde-form-item-dropdown-new-item select:last-child');
  $('#wpbody-content').on( 'change', '.pde-form-item-dropdown-new-item select:last-child', function(e) {
    key = parseInt($(e.target).attr('name').match(/([0-9]+).$/)[1]);
    key = key + 1;
    newName = $(e.target).attr('name').replace(/([0-9]+).$/, '' + key + ']');
    key = parseInt($(e.target).attr('id').match(/_([0-9]+)$/)[1]);
    key = key + 1;
    newid = $(e.target).attr('id').replace(/_([0-9]+)$/, '_' + key);
    clone = $(e.target).clone();
    $(clone).attr('id', newid);
    $(clone).attr('name', newName);
    $(clone).val(0);
    $("<br/>").appendTo($(e.target).parent());
    $(clone).appendTo($(e.target).parent());
  });
})(jQuery);
</script>
<?php endif; ?>
<@php 
