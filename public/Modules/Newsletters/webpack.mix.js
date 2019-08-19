const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public/modules/newsletters/assets').mergeManifest();

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
	   /* Editor assets */
    .js(__dirname + '/Resources/assets/js/editor.js', 'editor.js')
    .sass(__dirname + '/Resources/assets/sass/editor.scss', 'editor.css', {
      outputStyle: 'compressed'
    })

	   /* Email template assets */
    .sass(__dirname + '/Resources/assets/sass/email.scss', 'email.css', {
      outputStyle: 'compressed'
    });

if (mix.inProduction()) {
    mix.version();
}