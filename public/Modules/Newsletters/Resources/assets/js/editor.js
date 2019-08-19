/**
 * GrapesJS editor
 */

var grapesjs = require('grapesjs');

import pluginTooltip from 'grapesjs-tooltip';

require('grapesjs-preset-newsletter');

window.editor = grapesjs.init({
  container: '#gjs',
  fromElement: true,
  noticeOnUnload: false,
  avoidInlineStyle: true,
  layerManager: {
    showWrapper: 0,
  },
  colorPicker: { appendTo: 'parent', offset: { top: 26, left: -166, }, },
  plugins: [
    pluginTooltip,
    'gjs-preset-newsletter',
		editor => {
      var pnm = editor.Panels;
      pnm.addButton('options', [{
        id: 'undo',
        className: '',
				label: '<i class="material-icons">undo</i>',
        command: 'undo',
        attributes: { title: 'Undo (CTRL/CMD + Z)'}
      },{
        id: 'redo',
        className: '',
				label: '<i class="material-icons">redo</i>',
        command: 'redo',
        attributes: { title: 'Redo (CTRL/CMD + SHIFT + Z)' }
      }]);

			editor.Panels.getButton('views', 'open-sm').set({ // Style Manager
        className: '',
				label: '<i class="material-icons">palette</i>'
			});
			editor.Panels.getButton('views', 'open-layers').set({
        className: '',
				label: '<i class="material-icons">menu</i>'
			});
			editor.Panels.getButton('views', 'open-tm').set({
        className: '',
				label: '<i class="material-icons">build</i>'
			});
			editor.Panels.getButton('views', 'open-blocks').set({
        className: '',
				label: '<i class="material-icons">apps</i>'
			});

			editor.Panels.getButton('options', 'fullscreen').set({
        className: '',
				label: '<i class="material-icons">fullscreen</i>'
			});
			editor.Panels.getButton('options', 'sw-visibility').set({
        className: '',
				label: '<i class="material-icons">flip_to_back</i>'
			});
/*
			editor.Panels.getButton('options', 'gjs-toggle-images').set({
        className: '',
				label: '<i class="material-icons">compare</i>'
			});
			editor.Panels.getButton('options', 'gjs-open-import-template').set({
        className: '',
        title: 'Import template',
				label: '<i class="material-icons">cloud_upload</i>'
			});
			editor.Panels.getButton('options', 'export-template').set({
        className: '',
        attributes: {
          title: 'Export template'
        },
				label: '<i class="material-icons">cloud_download</i>'
			});
*/

			editor.Panels.getButton('devices-c', 'deviceDesktop').set({
        className: '',
				label: '<i class="material-icons">desktop_windows</i>'
			});
			editor.Panels.getButton('devices-c', 'deviceTablet').set({
        className: '',
				label: '<i class="material-icons">tablet_android</i>'
			});
			editor.Panels.getButton('devices-c', 'deviceMobile').set({
        className: '',
				label: '<i class="material-icons">phone_android</i>'
			});
		}
  ],
  pluginsOpts: {
    'gjs-preset-newsletter': {
/*      modalLabelImport: 'Paste all your code here below and click import',
      modalLabelExport: 'Copy the code and use it wherever you want',
      modalTitleImport: 'Import template',
      codeViewerTheme: 'material',
      //defaultTemplate: templateImport,
      importPlaceholder: '<table class="table"><tr><td class="cell">Hello world!</td></tr></table>',
      cellStyle: {
        'font-size': '12px',
        'font-weight': 300,
        'vertical-align': 'top',
        color: 'rgb(111, 119, 125)',
        margin: 0,
        padding: 0,
      }*/
    }
  },
  height: '100%',
  width: '100%',
  // Disable the storage manager for the moment
  storageManager: { type: null },
  //blockManager: false,
  assetManager: {
    storageType: '',
    storeOnChange: true,
    storeAfterUpload: true,
    upload: APP_URL + '/newsletters/editor/assets/upload',
    assets: [],
    uploadFile: function(e) {
      var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
      var formData = new FormData();
      formData.append('_token', csrf_token);
      for(var i in files){
        formData.append('file-' + i, files[i]);
      }
      formData.append('count', files.length);
      $.ajax({
        url: APP_URL + '/newsletters/editor/assets/upload',
        type: 'POST',
        data: formData,
        contentType: false,
        crossDomain: true,
        dataType: 'json',
        mimeType: "multipart/form-data",
        processData: false,
        success: function(result) {
          editor.AssetManager.add(result);
        }
      });
    },
  }
});

var jqxhr = $.ajax({
  url: APP_URL + '/newsletters/editor/assets',
  data: {_token: csrf_token},
  method: 'POST'
})
.done(function(data) {
  const am = editor.AssetManager;
  am.add(data);
})
.fail(function() {
  console.log('Error loading GrapesJS assets');
})
.always(function() {
});

editor.on('load', function() {
  editor.Panels.getButton('views', 'open-blocks').set('active', true);

  editor.Panels.removeButton('options', 'gjs-toggle-images');
  editor.Panels.removeButton('options', 'gjs-open-import-template');
  editor.Panels.removeButton('options', 'export-template');
  /*
  var openSmBtn = editor.Panels.getButton('views', 'open-sm');

  openSmBtn.set('attributes', {
    title: 'Style Manager'
  });*/
});
/*
editor.addComponents(`
<link rel="stylesheet" href="/modules/newsletters/assets/email.css">
`);
*/