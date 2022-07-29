var gulp = require('gulp');
var webpackStream = require('webpack-stream');
var webpack = require('webpack');
var gulpCopy = require('gulp-copy');

var project = 'gf-post-content-fields';

var build = './js/dist/';
var buildInclude = [
    '**/*.php',
    '**/*.html',
    '**/*.css',
    '**/*.js',
    '**/*.svg',
    '**/*.ttf',
    '**/*.otf',
    '**/*.eot',
    '**/*.woff',
    '**/*.woff2',
    '!node_modules/**/*',
    '!js/src/**/*',
    '!style.css.map',
    '!assets/js/custom/*',
    '!assets/css/patrials/*'
];

gulp.task('compile-vue', function() {
    return gulp.src('js/src/main.js')
        .pipe(webpackStream(require('./webpack.config.js'), webpack))
        .pipe(gulp.dest('js/dist/'));
});

gulp.task('copy-build', function() {
    return gulp.src(buildInclude)
        .pipe(gulp.dest('build/'));
});

gulp.task('release', function(callback) {
    runSequence(
        'compile-vue',
        'copy-build',
        function(error) {
            if (error) {
                console.log(error.message);
            } else {
                console.log('RELEASE FINISHED SUCCESSFULLY');
            }
            callback(error);
        });
});

gulp.task('default', function() {
    return gulp.src('js/src/main.js')
        .pipe(webpackStream(require('./webpack.config.js'), webpack))
        .pipe(gulp.dest('js/dist/'));
});