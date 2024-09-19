const gulp = require('gulp');
const { parallel, series } = require('gulp');
const gutil = require('gulp-util');
const fs = require('fs');
const path = require('path');
const execSync = require('child_process').execSync;
const cache = require('gulp-cached');
const dependents = require('gulp-dependents');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass')(require('sass'));
const glob = require('gulp-sass-glob');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');
const typescript = require('gulp-typescript');
const tsProject = typescript.createProject('./tsconfig.json');
const fileinclude = require('gulp-file-include');
const clean = require('gulp-clean');
const eslint = require('gulp-eslint');
const babel = require('gulp-babel');
let drupalInfo;
let drushCommand = 'drush';
let root = gutil.env.root;
let ddevStatus = false;
let watchStatus = false;
let config = {
  css: {
    src: ['exo*/src/scss/**/*.scss', 'exo*/src/ExoTheme/**/scss/**/*.scss', 'exo*/src/ExoThemeProvider/**/scss/**/*.scss'],
    includePaths: [],
  },
  js: {
    dest: 'js',
    src: ['exo*/src/js/**/*.js'],
  },
  ts: {
    dest: 'js',
    src: ['exo*/src/ts/*.ts'],
    watch: ['exo*/src/ts/**/*.ts'],
  },
};

function drupal(cb) {
  let command = drushCommand + ' status --format=json';
  let localRoot = testDir(splitPath(__dirname));
  if (root) {
    localRoot = testDir(splitPath(root));
    process.chdir(root);
  }
  drupalInfo = JSON.parse(execSync(command).toString());
  drupalInfo.root = localRoot + '/web';
  cb();
}

function exo(cb) {
  const root = process.env.DDEV_EXTERNAL_ROOT || drupalInfo['root'];
  let command = drushCommand + ' exo-scss';
  if (ddevStatus) {
    command = 'ddev exec "export DDEV_EXTERNAL_ROOT=' + root + ' && drush exo-scss"';
  }
  else if (root) {
    command += ' --root="' + root + '"';
  }
  execSync(command);
  config.css.includePaths.push(root + '/' + drupalInfo['site'] + '/files/exo');
  cb();
}

function js(cb) {
  return gulp.src(config.js.src)
    .pipe(plumber())
    .pipe(eslint({
      configFile: './.eslintrc',
      useEslintrc: false
    }))
    .pipe(eslint.format())
    .pipe(babel({
        presets: ['@babel/preset-env']
    }))
    .pipe(uglify())
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/src/js', '/' + config.js.dest);
    }))
    .pipe(plumber.stop())
    .pipe(gulp.dest('.'));
}

function ts(cb) {
  series(tsPackage, tsCompile, tsLint, tsClean)(cb);
}

function tsPackage(cb) {
  return gulp.src(config.ts.src)
    .pipe(plumber())
    .pipe(fileinclude({
      prefix: 'TS',
      basepath: '@file'
    }))
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/src/ts', '/tmp');
    }))
    .pipe(plumber.stop())
    .pipe(gulp.dest('.'));
};

function tsCompile(cb) {
  return gulp.src(['exo*/tmp/*.ts'])
    .pipe(cache('ts'))
    .pipe(plumber(() => {}))
    .pipe(tsProject(typescript.reporter.nullReporter()))
    .pipe(babel({
      presets: ['@babel/preset-env']
    }))
    .pipe(uglify())
    .pipe(rename(function(path) {
      path.dirname = path.dirname.replace('/tmp', '/' + config.ts.dest);
    }))
    .pipe(plumber.stop())
    .pipe(gulp.dest('.'));
}

function tsLint(cb) {
  return gulp.src(['exo*/src/ts/**/*.ts'])
    .pipe(plumber())
    .pipe(tsProject())
    .pipe(plumber.stop());
}

function tsClean(cb) {
  return gulp.src('exo*/tmp', {read: false})
    .pipe(clean());
};

function css(cb) {
  return gulp.src(config.css.src)
    .pipe(glob())
    .pipe(cache('css'))
    .pipe(dependents())
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: config.css.includePaths,
      silenceDeprecations: ['legacy-js-api']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      browserlist: ['last 2 versions'],
      cascade: false
    }))
    .pipe(rename(function(path) {
      var matches;
      path.dirname = path.dirname.replace('scss', 'css');
      path.dirname = path.dirname.replace('/src', '');
      // exoTheme Support.
      matches = path.dirname.match(/ExoTheme\/(.*)\//);
      var exoTheme = (matches && typeof matches[1] !== 'undefined') ? matches[1] : null;
      if (exoTheme) {
        path.dirname = path.dirname.replace('/ExoTheme', '');
        path.dirname = path.dirname.replace('/' + exoTheme, '') + ('/' + exoTheme);
      }
      // exoThemeProvider Support.
      matches = path.dirname.match(/ExoThemeProvider\/(.*)\//);
      var exoThemeProvider = (matches && typeof matches[1] !== 'undefined') ? matches[1] : null;
      if (exoThemeProvider) {
        path.dirname = path.dirname.replace('/ExoThemeProvider', '');
        path.dirname = path.dirname.replace('/' + exoThemeProvider, '');
      }
    }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('.'));
}

function enableDdev(cb) {
  drushCommand = 'ddev drush';
  ddevStatus = true;
  cb();
}

function enableWatch(cb) {
  watchStatus = true;
  cb();
}

function watch(cb) {
  if (watchStatus) {
    gulp.watch(config.css.src, css);
    gulp.watch(config.js.src, js);
    gulp.watch(config.ts.watch, ts);
  }
  else {
    cb();
  }
}

function splitPath(path) {
  var parts = path.split(/(\/|\\)/);
  if (!parts.length) return parts;
  return !parts[0].length ? parts.slice(1) : parts;
}

function testDir(parts) {
  if (parts.length === 0) return null;
  var p = parts.join('');
  var itdoes = fs.existsSync(path.join(p, '.ddev'));
  return itdoes ? p.slice(0, -1) : testDir(parts.slice(0, -1));
}

// exports.default = parallel(js, css, ts);
exports.default = series(drupal, exo, parallel(css));
exports.watch = series(drupal, enableWatch, exo, parallel(css, js, ts), watch);

exports.ddev = series(enableDdev, drupal, exo, parallel(css, js, ts));
exports.ddevWatch = series(enableWatch, enableDdev, drupal, exo, parallel(css, js, ts), watch);
