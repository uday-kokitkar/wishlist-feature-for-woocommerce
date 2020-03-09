const gulp 			= require('gulp');
const cleanCSS 		= require('gulp-clean-css');
const autoprefixer 	= require('gulp-autoprefixer');
const uglify 		= require('gulp-uglify');

var minify_admin_css = function () {
	return gulp.src( 'assets/css/dev/admin/*.css' )
		.pipe(cleanCSS({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}kb => ${details.stats.minifiedSize} kb`);
			}))
		.pipe(autoprefixer({cascade: false}))
		.pipe( gulp.dest( './assets/css/admin/' ) );
}

var minify_public_css = function () {
	return gulp.src( 'assets/css/dev/public/*.css' )
		.pipe(cleanCSS({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}kb => ${details.stats.minifiedSize} kb`);
			}))
		.pipe(autoprefixer({cascade: false}))
		.pipe( gulp.dest( './assets/css/public/' ) );
}

var minify_admin_js = function() {
	return gulp.src( 'assets/js/dev/admin/*.js' )
		.pipe(uglify())
		.pipe( gulp.dest( './assets/js/admin/' ) )
}

var minify_public_js = function() {
	return gulp.src( 'assets/js/dev/public/*.js' )
		.pipe(uglify())
		.pipe( gulp.dest( './assets/js/public/' ) )
}

exports.css 	= gulp.series( minify_admin_css, minify_public_css );
exports.js 		= gulp.series( minify_admin_js, minify_public_js );
exports.default = gulp.series( minify_admin_css, minify_public_css, minify_admin_js, minify_public_js );