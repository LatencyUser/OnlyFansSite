const mix = require('laravel-mix');
require('laravel-mix-purgecss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

let outputPath = 'public/css/theme';
if(typeof process.env.npm_config_outputPath !== 'undefined'){
    outputPath = process.env.npm_config_outputPath;
}

let resourcesPath = 'resources/sass/';
if(typeof process.env.npm_config_resourcePath !== 'undefined'){
    resourcesPath = process.env.npm_config_resourcePath;
}

mix
    .sass(resourcesPath+'/bootstrap.scss', outputPath)
    .sass(resourcesPath+'/bootstrap.rtl.scss', outputPath)
    .sass(resourcesPath+'/bootstrap.dark.scss', outputPath)
    .sass(resourcesPath+'/bootstrap.rtl.dark.scss', outputPath);
