const elixir = require('laravel-elixir');

/*require('laravel-elixir-vue-2');*/

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */


var bowerDirBootstrap = "bower_components/bootstrap-sass-official/assets/"; 
var bowerDirBootswatch = "bower_components/bootswatch-sass";
var bowerDirFontawesome = "bower_components/font-awesome/";
// data tables
var bowerDirDatatables = "bower_components/datatables-plugins/integration/";
// javascript
var bowerDirJquery = "bower_components/jquery/dist/";

// summernote
var bowerDirSummernote = "bower_components/summernote/dist/";

// chart.js
var bowerDirChartJs = "bower_components/chart.js/dist/";


elixir((mix) => {
    mix.sass('app.scss')
    	.copy(bowerDirBootstrap, 'resources/assets/sass/bootstrap')
    	.copy(bowerDirBootstrap + 'fonts/bootstrap/**', 'public/fonts')
    	.copy(bowerDirBootswatch, 'resources/assets/sass/bootswatch')
    	.copy(bowerDirFontawesome + 'scss', 'resources/assets/sass/fontawesome')
    	.copy(bowerDirFontawesome + 'fonts/**', 'public/fonts')
    	.copy(bowerDirJquery + 'jquery.js', 'resources/assets/js/jquery.js')
    	.copy(bowerDirBootstrap + 'javascripts/bootstrap.js', 'resources/assets/js/bootstrap.js')
    	.copy('bower_components/datatables/media/js/jquery.dataTables.js', 'resources/assets/js/')
    	.copy(bowerDirDatatables + 'bootstrap/3/dataTables.bootstrap.css', 'resources/assets/sass/others/dataTables.bootstrap.scss')
    	.copy(bowerDirDatatables + 'bootstrap/3/dataTables.bootstrap.js', 'resources/assets/js/')
        .copy(bowerDirSummernote+'summernote.js','resources/assets/js/')
        .copy(bowerDirSummernote+'summernote.css','resources/assets/sass/others/summernote.scss')
        .copy(bowerDirChartJs+'Chart.js','resources/assets/js/')
	mix.scripts([
			'js/jquery.js',
			'js/bootstrap.js',
			'js/jquery.dataTables.js',
            'js/jquery.autoNumeric.js',
			'js/dataTables.bootstrap.js',
            'js/summernote.js',
            'js/Chart.js',
            'js/utilities.js',
            'js/socket.io.js',
		],
		'public/js/app.js',
		'resources/assets'
	);
});

