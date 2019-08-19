const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public/modules/popups/assets').mergeManifest();
//mix.setPublicPath('assets').mergeManifest();

mix.options({
    processCssUrls: false,
    postCss: [require('autoprefixer')],
    uglify: {
      uglifyOptions: {
        warnings: false,
        parse: {},
        compress: {},
        mangle: true,
        output: null,
        toplevel: true,
        nameCache: null,
        ie8: true,
        keep_fnames: false,
      }
    },
});

mix
	   /* Lead assets */
    .js(__dirname + '/Resources/assets/js/leads.js', 'scripts.js')
    .sass(__dirname + '/Resources/assets/sass/leads.scss', 'style.css', {
      outputStyle: 'compressed'
    })

	 /* Lead modal assets */
   .js(__dirname + '/Resources/assets/js/lead-modal.js', 'modal.js')
   .sass(__dirname + '/Resources/assets/sass/lead-modal.scss', 'modal.css', {
      outputStyle: 'compressed'
    });

if (mix.inProduction()) {
    mix.version();
}