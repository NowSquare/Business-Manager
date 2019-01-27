<?php if ($showLabel && $options['label'] !== false && $options['label_show']): ?>
    <?= Form::customLabel($name, $options['label'], $options['label_attr']) ?>
<?php endif; ?>

<?php 
if ($showField): 
  $options['attr']['id'] = $name;
  $options['attr']['class'] = '';

  $disabled = (isset($options['attr']['disabled']) && $options['attr']['disabled'] == 'disabled') ? true : false;
  $readonly = ($disabled) ? 'true' : 'false';
  $show_edit = ($disabled) ? 'false' : 'true';
  $options['attr']['disabled'] = null;
?>
    <?= Form::textarea($name, $options['value'], $options['attr']) ?>

    <?php include base_path() . '/resources/views/vendor/laravel-form-builder/help_block.php' ?>
<script>
tinymce.init({
  selector: '#<?php echo $name ?>',
  language: '<?php echo app()->getLocale() ?>',
  content_css: '<?php echo url('assets/css/tinymce-content.min.css') ?>',
  readonly : <?php echo $readonly ?>,
  force_br_newlines : false,
  force_p_newlines : true,
  forced_root_block : '',
  plugins: 'advlist autolink link image media paste lists colorpicker textcolor contextmenu autoresize print preview anchor table code fullscreen',
<?php if ($disabled) { ?>
  toolbar: false,
<?php } else { ?>
  toolbar: 'fontsizeselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | removeformat',
<?php } ?>
  menubar: 'file edit insert view format table tools',
  menu: {
    file: {title: 'File', items: 'newdocument'},
    edit: {title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall'},
    insert: {title: 'Insert', items: 'link media | template hr'},
    view: {title: 'View', items: 'visualaid'},
    format: {title: 'Format', items: 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
    table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'},
    tools: {title: 'Tools', items: 'spellchecker code'}
  },
  paste_as_text: true,
/*  fontsize_formats: "0.75rem 1rem 1.25rem 1.5rem 1.75rem 2rem 2.25rem 2.5rem 2.75rem",*/
  menubar: <?php echo $show_edit ?>,
  statusbar: false,
  convert_urls: false,
  relative_urls: false,
  height: <?php echo $options['height'] ?>,
  autoresize_min_height: <?php echo $options['height'] ?>,
  autoresize_bottom_margin: 0,
  autoresize_overflow_padding: 0
});
</script>
<?php endif; ?>

<?php include base_path() . '/resources/views/vendor/laravel-form-builder/errors.php' ?>