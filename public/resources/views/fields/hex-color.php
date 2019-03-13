<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php if ($showField): ?>
    <div class="input-group" id="<?php echo $name ?>_picker">
      <?= Form::input($type, $name, $options['value'], $options['attr']) ?>
      <span class="input-group-append">
        <span class="input-group-text colorpicker-input-addon" style="background: none"><i class="mdl-shadow--2dp"></i></span>
      </span>
    </div>

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>

<script>
$(function() {
  $('#<?php echo $name ?>_picker').colorpicker({
    format: 'hex',
    fallbackColor: '#FFFFFF',
    autoInputFallback: false
  });
});
</script>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
