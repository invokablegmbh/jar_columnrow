const { src, dest } = require('gulp');
const concat = require('gulp-concat');

const readmeBundle = () =>
  src([
    '../Index.rst',
    '../QuickStart/Index.rst',
    '../Configuration/*.rst',
  ])
  .pipe(concat('README.md'))
  .pipe(dest('../../'));

  exports.readmeBundle = readmeBundle;