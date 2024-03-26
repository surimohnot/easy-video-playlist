var gulp           = require('gulp'),
	plumber        = require('gulp-plumber'),
	sass           = require('gulp-sass')(require('node-sass')),
	postcss        = require('gulp-postcss'),
	autoprefixer   = require('gulp-autoprefixer'),
	combineMq      = require("css-mqpacker"),
	cssComb        = require('gulp-csscomb'),
	eslint         = require('gulp-eslint'),
	clean          = require('gulp-clean'),
	webpack        = require('webpack'),
	rtlCSS         = require('gulp-rtlcss'),
	rename         = require('gulp-rename'),
	customComb     = {
		"remove-empty-rulesets": true,
		"always-semicolon": true,
		"color-case": "lower",
		"block-indent": "\t",
		"color-shorthand": true,
		"element-case": "lower",
		"eof-newline": true,
		"leading-zero": true,
		"quotes": "single",
		"sort-order-fallback": "abc",
		"space-before-colon": "",
		"space-after-colon": " ",
		"space-before-combinator": " ",
		"space-after-combinator": " ",
		"space-between-declarations": "\n",
		"space-before-opening-brace": " ",
		"space-after-opening-brace": "\n",
		"space-after-selector-delimiter": "\n",
		"space-before-selector-delimiter": "",
		"space-before-closing-brace": "\n",
		"strip-spaces": true,
		"unitless-zero": true,
		"vendor-prefix-align": true
	},
	jsPartials       = [ '.backend/js/admin/admin.js', './backend/js/admin/partials/**/*.js', './backend/js/block/block.js', './backend/js/block/edit.js', './backend/js/block/save.js', './backend/js/block/index.js', './assets/scripts/front/front.js', './assets/scripts/front/partials/**/*.js' ],
	cssPartials      = [ './backend/css/admin/partials/**/*.scss', './backend/css/admin/admin.scss' ],
	adminCss         = [ './backend/css/admin/admin.scss' ],
	frontCssPartials = [ './assets/styles/front/partials/**/*.scss', './assets/styles/front/front.scss' ],
	frontCss         = [ './assets/styles/front/front.scss' ];

// Compile Sass files to generate main css file.
function compileSass(css) {
	return gulp
		.src(css, {base: '.'})
		.pipe(plumber()) // Prevent termination on error
		.pipe(sass({
			indentType: 'tab',
			indentWidth: 1,
			outputStyle: 'expanded', // Expanded so that our CSS is readable
	  	})).on('error', sass.logError)
	  	.pipe(autoprefixer({
			cascade: false
		}))
		.pipe(cssComb(customComb))
	  	.pipe(postcss([
			combineMq({
				sort: true,
			}),
		]))
		.pipe(gulp.dest('.')) // Output compiled files in the same dir as Sass sources
		.pipe(rtlCSS())
		.pipe(rename({suffix: '-rtl'}))
		.pipe(gulp.dest('./')); // Output compiled files in the same dir as Sass sources
}

// Initialize gulp for plugin development.
gulp.task( 'watch', function( done ) {

	// Watch back-end css partials for changes and preprare main stylesheet.
	gulp.watch( cssPartials, gulp.series(function(done) {
		compileSass(adminCss);
		done();
	}) );

	// Watch front-end css partials for changes and preprare main stylesheet.
	gulp.watch( frontCssPartials, gulp.series(function(done) {
		compileSass(frontCss);
		done();
	}) );

	// Watch front-end js partials for changes and preprare main script file.
	gulp.watch( jsPartials, gulp.series(
		function( done ) {
			webpack(require('./webpack.config.js'), function() {
				done();
			});
		}
	) );

	done();
} );

gulp.task('build', gulp.series(
	function() {
		return gulp.src('easy-video-playlist', {read: false, allowEmpty: true})
			.pipe(clean());
	},
	function() {
		return gulp.src([
			'./**',
			'!./easy-video-playlist/**/*',
			'!./node_modules/**/*',
			'!./gulpfile.js',
			'!./package-lock.json',
			'!./package.json',
			'!./webpack.config.js',
			'!./backend/**/*.scss',
			'!./.gitignore',
			'!./.babelrc',
		])
			.pipe(gulp.dest('./easy-video-playlist'));
	},
	function() {
		return gulp.src([
			'./easy-video-playlist/node_modules',
			'./easy-video-playlist/backend/css/admin/partials',
			'./easy-video-playlist/assets/styles/front/partials',
		], {allowEmpty: true})
			.pipe(clean());
	}
));
