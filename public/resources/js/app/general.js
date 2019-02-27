$(function(){
  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip({
    boundary: 'window',
    trigger: 'hover'
  });

  // Initialize popovers
  $('[data-toggle="popover"]').popover({
    boundary: 'window',
    trigger: 'hover',
    html: true
  });

  // Make Bootstrap tabs accessible by hash
  var hash = window.location.hash;
  if (hash == '') {
    // Open first tab in form (otherwise menu will be triggered)
    $('form ul.nav li:first a').tab('show');
  } else {
    $('ul.nav li a[href="' + hash + '"]').tab('show');
  }

  $('.nav-tabs li a').click(function (e) {
    window.location.hash = this.hash;

    $('a.tab-hash').each(function() {
      _href = $(this)[0].protocol + "//" + $(this)[0].host + $(this)[0].pathname + $(this)[0].search;
      $(this).attr('href', _href + window.location.hash);
    });
  });

  // Set hash also on page load
  $('a.tab-hash').each(function() {
    _href = $(this)[0].protocol + "//" + $(this)[0].host + $(this)[0].pathname + $(this)[0].search;
    $(this).attr('href', _href + window.location.hash);
  });

  $(window).on('hashchange', function(e) {
    var hash = window.location.hash;
    hash && $('ul.nav li a[href="' + hash + '"]').tab('show');
  });

  // Focus tabs with :invalid inputs
  $('#submit_form_with_tabs').click(function () {
    var $submit_button = $(this);
    var $form = $(this).closest('form');
    var invalid = false;
    $form.find('input:invalid').each(function () {
      // Get current tab
      var active_tab = $('.tab-pane.active').attr('id');

      // Find the tab-pane that this element is inside, and get the id
      var $closest = $(this).closest('.tab-pane');
      var id = $closest.attr('id');

      // Find the link that corresponds to the pane and have it show
      $('.nav a[href="#' + id + '"]').tab('show');

      if (active_tab != id) {
        invalid = true;
      }

      // Only want to do it once
      return false;
    });

    if (invalid) {
      setTimeout(function() {
        // Submit again to show message
        $('#submit_form_with_tabs').trigger('click');
      }, 200);
    }
  });

  // Prevent double submit
  $('form').on('submit',function(e){
    var $form = $(this);

    if ($form.data('submitted') === true) {
      // Previously submitted - don't submit again
      e.preventDefault();
    } else {
      // Mark it so that the next submit can be ignored
      $form.data('submitted', true);
    }
  });

  // Focus tabs with error message
  $('.form-error-msg').each(function () {
    var active_tab = $('.tab-pane.active').attr('id');
    var $closest = $(this).closest('.tab-pane');
    var id = $closest.attr('id');
    $('.nav a[href="#' + id + '"]').tab('show');
    return false;
  });

  // Create selectize selects
  $('.selectize').selectize({
    persist: false
  });

  $('.selectize-tags').selectize({
    delimiter: ',',
    persist: false,
    create: function(input) {
      return {
        value: input,
        text: input
      }
    }
  });

  /*
   * Generate random string
   */
  window.randomString = function(string_length) {
    if (typeof string_length === 'undefined') string_length = 8;
    var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
      var rnum = Math.floor(Math.random() * chars.length);
      randomstring += chars.substring(rnum, rnum + 1);
    }
    return randomstring;
  }

  /*
   * Generate random number code
   */
  window.randomCode = function(string_length) {
    if (typeof string_length === 'undefined') string_length = 8;
    var chars = "0123456789";
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
      var rnum = Math.floor(Math.random() * chars.length);
      randomstring += chars.substring(rnum, rnum + 1);
    }
    return randomstring;
  }

  /*
   * Toggle password field visibility
   */
  window.togglePasswordField = function(field_name, classes, show) {
    if (show) {
      var pwd = $('#' + field_name).val();
      $('#' + field_name).attr('id', field_name + '2');
      $('#' + field_name + '2').after($('<input id="' + field_name + '" name="' + field_name + '" class="' + classes + '" autocapitalize="off" autocorrect="off" autocomplete="off" type="text">'));
      $('#' + field_name + '2').remove();
      $('#' + field_name).val(pwd);
    } else {
      var pwd = $('#' + field_name).val();
      $('#' + field_name).attr('id', field_name + '2');
      $('#' + field_name + '2').after($('<input id="' + field_name + '" name="' + field_name + '" class="' + classes + '" type="password">'));
      $('#' + field_name + '2').remove();
      $('#' + field_name).val(pwd);
    }
  }
});