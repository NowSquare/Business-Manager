<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php if ($showField): ?>
<?php
  $type = $options['type'] ?? $type;
?>
    <div class="input-group">
      <?= Form::input($type, $name, $options['value'], $options['attr']) ?>
      <span class="input-group-append" id="basic-addon2">
        <span class="input-group-text"><?php echo $options['text'] ?? '' ?></span>
      </span>
    </div>

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
