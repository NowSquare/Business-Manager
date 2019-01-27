<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php if ($showField): ?>
    <div class="row gutters-xs">
      <div class="col">
        <div class="input-icon">
          <span class="input-icon-addon">
            <i class="material-icons" style="font-size:16px">lock</i>
          </span>
          <?= Form::input($type, $name, $options['value'], $options['attr']) ?>
        </div>
      </div>
      <span class="col-auto">
        <button id="toggle_<?php echo $name ?>" class="btn btn-secondary" type="button" data-toggle="tooltip" title="<?php echo trans('g.show_password') ?>"><i class="fe fe-eye"></i></button>
      </span>
      <span class="col-auto">
        <button onclick="$('#<?php echo $name ?>').val(randomString(8));" class="btn btn-secondary" type="button" data-toggle="tooltip" title="<?php echo trans('g.generate_password') ?>"><i class="fe fe-refresh-cw"></i></button>
      </span>
    </div>
    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<script>
$('#toggle_<?php echo $name ?>').on('click', function() {
  if(! $(this).hasClass('active')) {
    $(this).addClass('active');
    $(this).find('i').removeClass('fe-eye').addClass('fe-eye-off');
    $(this).attr('data-original-title', "<?php echo trans('g.hide_password') ?>").tooltip('show');
    togglePasswordField('<?php echo $name ?>', 'form-control', true);
  } else {
    $(this).removeClass('active');
    $(this).find('i').removeClass('fe-eye-off').addClass('fe-eye');
    $(this).attr('data-original-title', "<?php echo trans('g.show_password') ?>").tooltip('show');
    togglePasswordField('<?php echo $name ?>', 'form-control', false);
  }
});
</script>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
