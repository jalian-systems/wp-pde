<script type="text/javascript">
// Custom example logic
jQuery(document).ready(function($) {
  var uploader = new plupload.Uploader({
    runtimes : 'gears,html5,flash,silverlight,browserplus',
    browse_button : 'submit-pdepluginaddfile-multi-select',
    container : 'plupload-container',
    max_file_size : '10mb',
    url : '<?php echo plugins_url('plupload.php', __FILE__ ); ?>',
    flash_swf_url : '/plupload/js/plupload.flash.swf',
    silverlight_xap_url : '/plupload/js/plupload.silverlight.xap',
  });

  uploader.bind('Init', function(up, params) {
    $('#plupload-filelist').html("<div>Current runtime: " + params.runtime + "</div>");
    $('#submit-pdepluginaddfile-multi').attr('disabled', 'disabled');
  });

  $('#submit-pdepluginaddfile-multi').click(function(e) {
    uploader.start();
    e.preventDefault();
  });

  uploader.init();

  uploader.bind('FilesAdded', function(up, files) {
    $.each(files, function(i, file) {
      $('#plupload-filelist').append(
        '<div id="' + file.id + '">' +
        file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
      '</div>');
      $('#plupload-filelist').append(
        '<input value="' + file.name + '" ' +
                'name="files[]" type="hidden" />' );
    });

    $('#submit-pdepluginaddfile-multi').removeAttr('disabled');
    up.refresh(); // Reposition Flash/Silverlight
  });

  uploader.bind('UploadProgress', function(up, file) {
    $('#' + file.id + " b").html(file.percent + "%");
  });

  uploader.bind('Error', function(up, err) {
    $('#plupload-filelist').append("<div>Error: " + err.code +
      ", Message: " + err.message +
      (err.file ? ", File: " + err.file.name : "") +
      "</div>"
    );

    up.refresh(); // Reposition Flash/Silverlight
  });

  uploader.bind('FileUploaded', function(up, file) {
    $('#' + file.id + " b").html("Done");
  });

  uploader.bind('UploadComplete', function(up, file) {
    $('#pde-plugin-add-file-multi').submit();
  });
});
</script>
