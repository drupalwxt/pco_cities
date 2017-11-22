/**
 * @file
 *
 * Defines gulp tasks to be run by Gulp task runner.
 */

const gulp = require('gulp');
const sass = require('gulp-sass');
const browserSync = require('browser-sync');

// Browser Sync Settings
const browserSyncProxy = 'http://challenge.dev/';
const browserSyncPort = 3000;

// Child Process for running drush commands
const childProcess = require('child_process');

// Drush Commands
gulp.task('drush:cr', function(done){
  'use strict';

  return childProcess.spawn('drush', ['cr'], {stdio: 'inherit'})
          .on('close', done);
});

// Browser Sync Manual Reload
gulp.task('browserSyncReload', function() {
  'use strict';

  browserSync.reload();
});

// SASS Compilation
gulp.task('sass', function() {
  'use strict';

  gulp.src('sass/style.scss')
    .pipe(sass({ outputStyle: 'compressed' })
      .on('error', sass.logError))
    .pipe(gulp.dest('css'))
    .pipe(browserSync.stream());
});

gulp.task('sass:watch', ['sass'], function(){
  'use strict';

  gulp.watch('sass/**/**.scss', ['sass','drush:cr', 'browserSyncReload']);
});

// Browser Sync
gulp.task('browser-sync', function(){
  'use strict';

  browserSync({
    proxy: browserSyncProxy,
    port: browserSyncPort
  });

});

// Template File Changes
gulp.task('twig:watch', function(){
  'use strict';

  gulp.watch('templates/**/**.html.twig', ['drush:cr', 'browserSyncReload']);
});

// Assets Watch
gulp.task('assets:watch', function(){
  'use strict';

  gulp.watch(['assets/**/**.*'], ['drush:cr','browserSyncReload']);
});

// Default Task
gulp.task('default', ['sass']);

// Dev Environment tasks
gulp.task('dev', ['browser-sync','sass:watch','twig:watch','assets:watch']);
