import concat from 'gulp-concat';
import gulp from 'gulp';
import gulpif from 'gulp-if';
import livereload from 'gulp-livereload';
import merge from 'merge-stream';
import order from 'gulp-order';
import sass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import uglify from 'gulp-uglify';
import uglifycss from 'gulp-uglifycss';
import upath from 'upath';
import yargs from 'yargs';

const { argv } = yargs
  .options({
    rootPath: {
      description: '<path> path to web assets directory',
      type: 'string',
      requiresArg: true,
      required: false,
      default: '../../web/assets',
    },
    vendorPath: {
      description: '<path> path to vendor directory',
      type: 'string',
      requiresArg: true,
      required: false,
      default: '../../vendor',
    },
    nodeModulesPath: {
      description: '<path> path to node_modules directory',
      type: 'string',
      requiresArg: true,
      required: false,
      default: '../../node_modules',
    },
  });

const env = process.env.GULP_ENV;
const rootPath = upath.normalizeSafe(argv.rootPath);
const adminRootPath = upath.joinSafe(rootPath, 'app/admin');
const shopRootPath = upath.joinSafe(rootPath, 'app/shop');
// const vendorPath = upath.normalizeSafe(argv.vendorPath);
// const nodeModulesPath = upath.normalizeSafe(argv.nodeModulesPath);

const paths = {
  admin: {
    js: [
      'Resources/private/admin/js/**',
    ],
    sass: [
      'Resources/private/admin/sass/**',
    ],
    css: [
      'Resources/private/admin/css/**',
    ],
    img: [
      'Resources/private/admin/img/**',
    ],
  },
  shop: {
    js: [
      'Resources/private/shop/js/**',
    ],
    sass: [
      'Resources/private/shop/sass/**',
    ],
    css: [
      'Resources/private/shop/css/**',
    ],
    img: [
      'Resources/private/shop/img/**',
    ],
  },
};

const sourcePathMap = [
  {
    sourceDir: 'Resources/private/',
    destPath: '/AppBundle',
  },
];

const mapSourcePath = function mapSourcePath(sourcePath /* , file */) {
  const match = sourcePathMap.find(({ sourceDir }) => (
    sourcePath.substring(0, sourceDir.length) === sourceDir
  ));

  if (!match) {
    return sourcePath;
  }

  const { sourceDir, destPath } = match;

  return upath.joinSafe(destPath, sourcePath.substring(sourceDir.length));
};

export const buildAdminJs = function buildAdminJs() {
  return gulp.src(paths.admin.js, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(concat('app.js'))
    .pipe(gulpif(env === 'prod', uglify()))
    .pipe(gulpif(env !== 'prod', sourcemaps.mapSources(mapSourcePath)))
    .pipe(gulpif(env !== 'prod', sourcemaps.write('./')))
    .pipe(gulp.dest(upath.joinSafe(adminRootPath, 'js')))
    .pipe(livereload());
};
buildAdminJs.description = 'Build admin js assets.';

export const buildAdminCss = function buildAdminCss() {
  const cssStream = gulp.src(paths.admin.css, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(concat('css-files.css'));

  const sassStream = gulp.src(paths.admin.sass, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(sass())
    .pipe(concat('sass-files.scss'));

  return merge(cssStream, sassStream)
    .pipe(order(['css-files.css', 'sass-files.scss']))
    .pipe(concat('style.css'))
    .pipe(gulpif(env === 'prod', uglifycss()))
    .pipe(gulpif(env !== 'prod', sourcemaps.write('./')))
    .pipe(gulp.dest(upath.joinSafe(adminRootPath, 'css')))
    .pipe(livereload());
};
buildAdminCss.description = 'Build admin css assets.';

export const buildAdminImg = function buildAdminImg() {
  return gulp.src(paths.admin.img)
    .pipe(gulp.dest(upath.joinSafe(adminRootPath, 'img')));
};
buildAdminImg.description = 'Build admin img assets.';

export const watchAdmin = function watchAdmin() {
  livereload.listen();

  gulp.watch(paths.admin.js, buildAdminJs);
  gulp.watch(paths.admin.sass, buildAdminCss);
  gulp.watch(paths.admin.css, buildAdminCss);
  gulp.watch(paths.admin.img, buildAdminImg);
};
watchAdmin.description = 'Watch admin asset sources and rebuild on changes.';

export const buildShopJs = function buildShopJs() {
  return gulp.src(paths.shop.js, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(concat('app.js'))
    .pipe(gulpif(env === 'prod', uglify()))
    .pipe(gulpif(env !== 'prod', sourcemaps.write('./')))
    .pipe(gulp.dest(upath.joinSafe(shopRootPath, 'js')))
    .pipe(livereload());
};
buildShopJs.description = 'Build shop js assets.';

export const buildShopCss = function buildShopCss() {
  const cssStream = gulp.src(paths.shop.css, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(concat('css-files.css'));

  const sassStream = gulp.src(paths.shop.sass, { base: './' })
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(sass())
    .pipe(concat('sass-files.scss'));

  return merge(cssStream, sassStream)
    .pipe(order(['css-files.css', 'sass-files.scss']))
    .pipe(concat('style.css'))
    .pipe(gulpif(env === 'prod', uglifycss()))
    .pipe(gulpif(env !== 'prod', sourcemaps.write('./')))
    .pipe(gulp.dest(upath.joinSafe(shopRootPath, 'css')))
    .pipe(livereload());
};
buildShopCss.description = 'Build shop css assets.';

export const buildShopImg = function buildShopImg() {
  return gulp.src(paths.shop.img)
    .pipe(gulp.dest(upath.joinSafe(shopRootPath, 'img')));
};
buildShopImg.description = 'Build shop img assets.';

export const watchShop = function watchShop() {
  livereload.listen();

  gulp.watch(paths.shop.js, buildShopJs);
  gulp.watch(paths.shop.sass, buildShopCss);
  gulp.watch(paths.shop.css, buildShopCss);
  gulp.watch(paths.shop.img, buildShopImg);
};
watchShop.description = 'Watch shop asset sources and rebuild on changes.';

export const buildAdmin = gulp.parallel(buildAdminJs, buildAdminCss, buildAdminImg);
buildAdmin.description = 'Build admin assets.';

export const buildShop = gulp.parallel(buildShopJs, buildShopCss, buildShopImg);
buildShop.description = 'Build shop assets.';

export const build = gulp.parallel(buildAdmin, buildShop);
build.description = 'Build assets.';

export const watch = gulp.parallel(watchAdmin, watchShop);
watch.description = 'Watch asset sources and rebuild on changes.';

export default build;
