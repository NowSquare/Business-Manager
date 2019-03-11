<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>
<?php
$disabled = (isset($options['attr']['disabled']) && $options['attr']['disabled'] == 'disabled') ? true : false;
?>
<?php if ($showLabel && $options['label'] !== false && $options['label_show'] && ! $disabled): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php if ($showField): ?>
<?php if (! $disabled): ?>
    <div class="row gutters-xs">
      <div class="col">
        <div class="custom-file">
        <?= Form::input('file', $name, $options['value'], $options['attr']) ?>
<?php if ($options['file_name'] !== null) { ?>
          <label class="custom-file-label" id="imageLabel<?php echo $options['unique'] ?>"><?php echo $options['file_name']; ?></label>
<?php } else { ?>
          <label class="custom-file-label text-muted" id="imageLabel<?php echo $options['unique'] ?>"><?php echo $options['file_label']; ?></label>
<?php } ?>
        </div>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-secondary" onClick="deleteImage<?php echo $options['unique'] ?>()" data-toggle="tooltip" title="<?php echo trans('g.remove_image') ?>"><i class="fe fe-trash-2"></i></button>
      </div>
    </div>

    <input type="hidden" name="<?php echo $name ?>_changed" id="<?php echo $name ?>_changed" value="0">
<?php endif; ?>

<?php if (! $options['remote_preview']) { ?>
<?php if ($options['file_url'] !== null) { ?>
    <img id="imagePreview<?php echo $options['unique'] ?>" src="<?php echo $options['file_url'] ?>" class="<?php echo $options['preview']['class'] ?>" style="width:<?php echo $options['preview']['width'] ?>;height:<?php echo $options['preview']['height'] ?>;">
<?php } else { ?>
    <img id="imagePreview<?php echo $options['unique'] ?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="<?php echo $options['preview']['class'] ?> d-none" style="width:<?php echo $options['preview']['width'] ?>;height:<?php echo $options['preview']['height'] ?>;">
<?php } ?>
<?php } ?>

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>

<?php if (! $disabled): ?>
<script>
function setImage<?php echo $options['unique'] ?>(_this) {
  $('#<?php echo $name ?>_changed').val(1);
  var filePath = $(_this).val();
  var fileName = filePath.replace(/^.*\\/, "");

  var target = $(_this).next('.custom-file-label').attr('data-target');

  if (filePath != '') {
    $('#imageLabel<?php echo $options['unique'] ?>').removeClass('text-muted').html(fileName);
  } else {
    deleteImage<?php echo $options['unique'] ?>();
  }

  if (_this.files && _this.files[0]) {
    var target = $(_this).attr('id');
    var reader = new FileReader();

    reader.onload = function(e) {
      $('#imagePreview<?php echo $options['unique'] ?>').removeClass('d-none');
      $('#imagePreview<?php echo $options['unique'] ?>').attr('src', e.target.result);
    }

    reader.readAsDataURL(_this.files[0]);
  }
}

function deleteImage<?php echo $options['unique'] ?>() {
  $('#<?php echo $name ?>_changed').val(1);
  $('#<?php echo $name ?>').val("");
  $('#imageLabel<?php echo $options['unique'] ?>').addClass('text-muted').html("<?php echo $options['file_label']; ?>");
  $('#imagePreview<?php echo $options['unique'] ?>').addClass('d-none').attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
}
</script>
<?php endif; ?>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
