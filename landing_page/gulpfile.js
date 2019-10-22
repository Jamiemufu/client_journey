/*
 * Dependencies
 */
var css_minify   = require('gulp-cssnano');
var gulp         = require('gulp');
var js_minify    = require('gulp-uglify');
var less_compile = require('gulp-less');
var rename       = require('gulp-rename');


/*
 * Compile LESS
 */
gulp.task('compile_less', function()
{

    return gulp.src('public_html/src/css/styles.less')
               .pipe(less_compile())
               .pipe(css_minify({discardComments: {removeAll: true}}))
               .pipe(rename('styles.min.css'))
               .pipe(gulp.dest('public_html/_public/css'));

});


/*
 * Minify JavaScript
 */
gulp.task('minify_js', function()
{

    return gulp.src('public_html/src/js/scripts.js')
               .pipe(js_minify())
               .pipe(rename('scripts.min.js'))
               .pipe(gulp.dest('public_html/_public/js'));

});


/*
 * Default task
 */
gulp.task('default', ['compile_less', 'minify_js']);


/*
 * Watch for changes
 */
gulp.watch('public_html/src/js/scripts.js', function()
{
    gulp.run('minify_js');
});

gulp.watch('public_html/src/css/**/*.less', function()
{
    gulp.run('compile_less');
});