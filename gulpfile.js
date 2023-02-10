const gulp = require('gulp');
const { parallel, series } = require('gulp');
const gutil = require('gulp-util');
const execSync = require('child_process').execSync;
const cache = require('gulp-cached');
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
  let command = drushCommand;
  if (root) {
    command += ' --root=' + root;
  }
  drupalInfo = JSON.parse(execSync(command + ' status --format=json').toString());
  cb();
}

function exo(cb) {
  const root = process.env.DDEV_EXTERNAL_ROOT || drupalInfo['root'];
  if (ddevStatus) {
    execSync('ddev exec "export DDEV_EXTERNAL_ROOT=' + root + ' && drush exo-scss"');
  }
  else {
    execSync(drushCommand + ' exo-scss');
  }
  config.css.includePaths.push(root + '/' + drupalInfo['site'] + '/files/exo');
  cb();
}

function js(cb) {
  gulp.src(config.js.src)
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

  cb();
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

let doTsLint = false;
function tsCompile(cb) {
  return gulp.src(['exo*/tmp/*.ts'])
    .pipe(cache('ts'))
    .pipe(plumber(() => {
      doTsLint = true;
    }))
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
  console.log(doTsLint);
  return gulp.src(['exo*/src/ts/**/*.ts'])
    .pipe(plumber())
    .pipe(tsProject())
    .pipe(plumber.stop());
}

function tsClean(cb) {
  return gulp.src('exo*/tmp', {read: false})
    .pipe(clean());

  cb();
};

function css(cb) {
  gulp.src(config.css.src)
    .pipe(glob())
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: config.css.includePaths
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
    .pipe(gulp.dest('.'))
    ;
  cb();
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

// exports.default = parallel(js, css, ts);
exports.default = series(drupal, exo, parallel(css));
exports.watch = series(enableWatch, parallel(js, css, ts), watch);

exports.ddev = series(drupal, enableDdev, exo, parallel(css, js, ts));
exports.ddevWatch = series(drupal, enableWatch, enableDdev, exo, parallel(css, js, ts), watch);
