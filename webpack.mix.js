const cssImport = require('postcss-import')
const cssNesting = require('postcss-nesting')
const mix = require('laravel-mix');
const path = require('path');
const purgecss = require('@fullhuman/postcss-purgecss');
const tailwindcss = require('tailwindcss')

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

// Javascript
mix.js('resources/js/app.js', 'public/js');

// CSS
mix.css('~@fortawesome/fontawesome-free/css/all.css', 'public/css/fontawesome.css');

// Webfonts
mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts');

mix.postCss('resources/css/app.css', 'public/css', [
    cssImport(),
    cssNesting(),
    tailwindcss('tailwind.js'),
    ...mix.inProduction() ? [
        purgecss({
            content: ['./resources/views/**/*.blade.php', './resources/js/**/*.vue'],
            defaultExtractor: content => content.match(/[\w-/:.]+(?<!:)/g) || [],
            whitelistPatternsChildren: [/nprogress/],
        }),
    ] : [],
])
    
mix.webpackConfig({
    output: { chunkFilename: 'js/[name].js?id=[chunkhash]' },
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.runtime.esm.js',
            '@': path.resolve('resources/js'),
        },
    },
})
    .version()
    .sourceMaps()