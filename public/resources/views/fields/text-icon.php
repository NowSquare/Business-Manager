<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php if ($showField): ?>
    <div class="input-icon">
      <span class="input-icon-addon">
        <i class="material-icons" style="font-size:16px"><?php echo $options['prefix'] ?? '' ?></i>
      </span>
      <?= Form::input($type, $name, $options['value'], $options['attr']) ?>
    </div>

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
