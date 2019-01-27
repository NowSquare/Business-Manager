<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showField): ?>
<?php
  $options['checked'] = ($options['default_value'] === 0) ? false : true;
  if (isset($options['value'])) $options['checked'] = ($options['value'] === 0) ? false : true;
?>
    <input type="hidden" name="<?php echo $name ?>" value="0">
    <?= Form::checkbox($name, 1, $options['checked'], $options['attr']) ?>

    <?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
        <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
    <?php endif; ?>

<?php endif; ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($showField && $options['wrapper'] !== false): ?>
    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>
<?php endif; ?>
