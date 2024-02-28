let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */
//
// mix.js('js/vue.min.js', '../assets/js/vue.js')
//     .setPublicPath('../assets/');

mix.js('js/app.js', '../assets/js/recourse.js')
    .extract()
    // .sass('sass/app.scss', '../assets/css/')
    .setPublicPath('../assets/');
