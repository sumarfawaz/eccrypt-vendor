const gulp = require('gulp');
const browserSync = require('browser-sync').create();

// Static server
function browserSyncInit() {
    browserSync.init({
        proxy: "http://pbss.local", // Replace with your local WordPress site URL
        files: [
            '**/*.php',
            'assets/css/**/*.css',
            'assets/js/**/*.js'
        ], 
        notify: false,
    });
}

// Watch files for changes
function watchFiles() {
    gulp.watch("**/*.php").on('change', browserSync.reload);
    gulp.watch("assets/css/**/*.css").on('change', browserSync.reload);
    gulp.watch("assets/js/**/*.js").on('change', browserSync.reload);
}

exports.default = gulp.series(browserSyncInit, watchFiles);
