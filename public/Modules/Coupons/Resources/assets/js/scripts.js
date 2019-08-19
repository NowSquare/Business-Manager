try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

$(function() {

	/*
	 * Bootstrap tooltips
	 */

  $('[data-toggle*="tooltip"]').tooltip({
    trigger : 'hover'
  });
  $('[data-toggle*="popover"]').popover({
    html: true
  });

});