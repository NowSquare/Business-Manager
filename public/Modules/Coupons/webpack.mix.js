const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public/modules/coupons/assets').mergeManifest();
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
    .js(__dirname + '/Resources/assets/js/scripts.js', 'scripts.js')
    .sass(__dirname + '/Resources/assets/sass/style.scss', 'style.css', {
      outputStyle: 'compressed'
    });

if (mix.inProduction()) {
    mix.version();
}