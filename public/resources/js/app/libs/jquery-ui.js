/**
 * jQuery UI
 */

//require('webpack-jquery-ui/draggable');
//require('webpack-jquery-ui/droppable');
//require('webpack-jquery-ui/sortable');

window.draggable = require('webpack-jquery-ui/draggable');
window.droppable = require('webpack-jquery-ui/droppable');
window.sortable = require('webpack-jquery-ui/sortable');

// For elFinder
window.selectable = require('webpack-jquery-ui/selectable');
window.sortable = require('webpack-jquery-ui/sortable');
window.resizable = require('webpack-jquery-ui/resizable');
window.dialog = require('webpack-jquery-ui/dialog');
window.slider = require('webpack-jquery-ui/slider');
window.tabs = require('webpack-jquery-ui/tabs');

$(function() {
  $('.sortable').sortable({
    items: 'div.sort:not(.unsortable)',
  }).disableSelection();

  $('.reorder-rows').sortable({
    handle: '.sortable-handle',
    placeholder: 'reorder-rows-placeholder',
    forcePlaceholderSize: true,
    items: 'tr:not(.unsortable)'
  });
});