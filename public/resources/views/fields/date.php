<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    <div <?= $options['wrapperAttrs'] ?> >
    <?php endif; ?>
<?php endif; ?>

<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php
if ($showField): 
  $type = 'text';
?>

    <div class="input-icon">
      <span class="input-icon-addon">
        <i class="material-icons" style="font-size:16px">calendar_today</i>
      </span>
      <?= Form::input($type, $name . '_field', '', $options['attr']) ?>
    </div>

    <input type="hidden" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $options['value'] ?>">

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<script>
$(function() {
  $('[name=<?php echo $name ?>_field]').datepicker({
    todayHighlight: false,
    clearBtn: true,
<?php if($options['value'] !== null) { ?>
    defaultViewDate: moment('<?php echo $options['value'] ?>').format('YYYY-MM-DD'),
<?php } ?>
    format: {
      toDisplay: function (date, format, language) {
        $('#<?php echo $name ?>').val(moment(date).format('YYYY-MM-DD'));
        return moment(date).format('MMM Do YYYY');
      },
      toValue: function (date, format, language) {
        var d = new Date($('#<?php echo $name ?>').val());
        return new Date(d);
      }
    }
  }).on('changeDate', function(e) {
    if (e.dates.length === 0) {
      $('#<?php echo $name ?>').val('');
    }
  });

<?php if($options['value'] !== null) { ?>
  $('[name=<?php echo $name ?>_field]').val( moment($('#<?php echo $name ?>').val()).format('MMM Do YYYY') );
<?php } ?>
});
</script>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>

<?php if ($showLabel && $showField): ?>
    <?php if ($options['wrapper'] !== false): ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
