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

$disabled = (isset($options['attr']['disabled']) && $options['attr']['disabled']) ? true : false;
$readonly = (isset($options['attr']['readonly']) && $options['attr']['readonly'] == 1) ? true : false;

?>
    <div class="row gutters-xs">
      <div class="col">
        <div class="input-icon">
          <span class="input-icon-addon">
            <i class="material-icons" style="font-size:16px">calendar_today</i>
          </span>

          <?= Form::input($type, $name . '_field', '', $options['attr']) ?>
        </div>
      </div>
      <div class="col-auto">
        <select name="<?php echo $name . '_time' ?>" id="<?php echo $name . '_time' ?>" class="custom-select" style="width: 115px"<?php if ($disabled) echo ' disabled'; ?><?php if ($readonly) echo ' readonly'; ?>>
          <option value=""></option>
<?php
$time = mktime(0, 0, 0, 1, 1);

for ($i = 0; $i < 86400; $i += 900) {  // 1800 = half hour, 86400 = one day
  echo '<option value="' . date('H:i:00', $time + $i) . '">' . date(auth()->user()->getUserTimeFormat(), $time + $i) . '</option>'; 
}
?>
        </select>
      </div>
    </div>

    <input type="hidden" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $options['value'] ?>">

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<script>
$(function() {
  $('[name=<?php echo $name ?>_field]').datepicker({
    todayHighlight: true,
    autoclose: true,
    todayBtn: 'linked',
    clearBtn: true,
<?php if($options['value'] !== null) { ?>
    defaultViewDate: moment('<?php echo $options['value'] ?>').format('YYYY-MM-DD HH:mm:00'),
<?php } ?>
    format: {
      toDisplay: function (date, format, language) {
        var remainder = (parseInt(moment(date).minute()) == 0 || parseInt(moment(date).minute()) == 15 || parseInt(moment(date).minute()) == 30 || parseInt(moment(date).minute()) == 45) ? 0 :15 - (moment(date).minute() % 15);

        if ($('#<?php echo $name . '_time' ?>').val() == '') {
          var time = moment(date).add(remainder, "minutes").format('HH:mm:00');
          $('#<?php echo $name . '_time' ?>').val(time);
        } else {
          var time = $('#<?php echo $name . '_time' ?>').val();
        }
        $('#<?php echo $name ?>').val(moment(date).format('YYYY-MM-DD ' + time));
        return moment(date).format('MMM Do YYYY');
      },
      toValue: function (date, format, language) {
        var d = new Date($('#<?php echo $name ?>').val());
        return new Date(d);
      }
    }
  }).on('changeDate', function(e) {
    if (typeof e.dates === 'undefined') {
      var date = $('#<?php echo $name ?>').val();
      if (date == '') {
        $('#<?php echo $name ?>_time').val('');
      } else {
        var remainder = (parseInt(moment(date).minute()) == 0 || parseInt(moment(date).minute()) == 15 || parseInt(moment(date).minute()) == 30 || parseInt(moment(date).minute()) == 45) ? 0 :15 - (moment(date).minute() % 15);
        $('#<?php echo $name ?>_time').val(moment(date).add(remainder, "minutes").format('HH:mm:00'));
      }
    } else if (e.dates.length === 0) {
      $('#<?php echo $name ?>').val('');
      $('#<?php echo $name ?>_time').val('');
    } else {
      $('#<?php echo $name ?>_time').val('09:00:00');
    }
  });

  $('#<?php echo $name . '_time' ?>').on('change', function() {
    var date = $('#<?php echo $name ?>').val();
    if (date != '') {
      $('#<?php echo $name ?>').val(moment(date).format('YYYY-MM-DD ' + $('#<?php echo $name ?>_time').val()));
    }
  });

<?php if($options['value'] !== null) { ?>
  $('[name=<?php echo $name ?>_field]').val( moment($('#<?php echo $name ?>').val()).format('MMM Do YYYY') );

  var date = $('#<?php echo $name ?>').val();
  if (date == '') {
    $('#<?php echo $name ?>_time').val('');
  } else {
    var remainder = (parseInt(moment(date).minute()) == 0 || parseInt(moment(date).minute()) == 15 || parseInt(moment(date).minute()) == 30 || parseInt(moment(date).minute()) == 45) ? 0 :15 - (moment(date).minute() % 15);
    $('#<?php echo $name ?>_time').val(moment(date).add(remainder, "minutes").format('HH:mm:00'));
  }
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
