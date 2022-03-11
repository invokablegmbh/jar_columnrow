const { src, dest } = require('gulp');
const concat = require('gulp-concat');

const readmeBundle = () =>
  src([
    '../Includes.rst.txt',
    '../Index.rst',
    '../QuickStart/Index.rst',
    '../Configuration/*.rst',
  ])
  .pipe(concat('Index.rst'))
  .pipe(dest('./'));

  exports.readmeBundle = readmeBundle;