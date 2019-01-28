
window.dt = require('datatables.net');

require('datatables.net-bs4');
require('datatables.net-buttons-dt');
require('datatables.net-buttons-bs4');

// General config
$.extend(true, $.fn.dataTable.defaults, {
  searching: true,
  ordering: true,
  serverSide: true,
  processing: true,
  stateSave: true,
  autoWidth: false,
  responsive: false,
  dom:  "<'row'<'col-7 col-md-5'<'float-left ml-1'f><'float-left ml-2'l>><'col-5 col-md-7 text-right'<'mr-3'B>>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-12 col-md-5'<'ml-3 text-muted'i>><'col-sm-12 col-md-7'<'mr-3 mt-2'p>>>",
  language: {
  lengthMenu: "_MENU_",
  processing: '<div class="text-center" style="width:100%;margin:1rem 0"><div class="loader" style="margin:auto"></div></div>',
  info: "Showing _START_ to _END_ of _TOTAL_ entries",
  infoEmpty: "No entries to show",
  zeroRecords: "No records found",
  search: "",
  searchPlaceholder: "Search...",
  paginate: {
      first: "First",
      last: "Last",
      next: "Next",
      previous: "Previous"
  },
  },
  lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] ],
});