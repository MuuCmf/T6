// 通用gulp文件，不推荐使用通用方法，建议在各模块目录单独建立

var gulp        = require('gulp'),
    sass        = require('gulp-sass'),
    minifyCss   = require('gulp-minify-css'),
    plumber     = require('gulp-plumber'),
    babel       = require('gulp-babel'),
    uglify      = require('gulp-uglify'),
    copy        = require('gulp-contrib-copy'),
    concat      = require('gulp-concat'),
    rename      = require('gulp-rename'),
    browserSync = require('browser-sync').create(),
    reload      = browserSync.reload;
    
// 定义源代码的目录和编译压缩后的目录
var src='./_src/',
    dist='../../public/static/articles';
// 编译全部scss 并压缩
gulp.task('admin_scss', function(){
    gulp.src([
            src + 'admin/css/**/*.scss',
        ])
        .pipe(sass())
        .pipe(concat('main.css'))//合并css
        .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
        .pipe(minifyCss())
        .pipe(gulp.dest(dist + '/admin/css'));
})

gulp.task('pc_scss', function(){
    gulp.src([
            src + 'pc/css/**/*.scss',
        ])
        .pipe(sass())
        .pipe(concat('main.css'))//合并css
        .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
        .pipe(minifyCss())
        .pipe(gulp.dest(dist + '/pc/css'));
})

gulp.task('diy_scss', function(){
    gulp.src([
            src + 'diy/**/*.scss',
        ])
        .pipe(sass())
        .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
        .pipe(minifyCss())
        .pipe(gulp.dest(dist + '/diy'));
})

// 编译模块js 并压缩不合并
gulp.task('admin_js', function() {
      gulp.src([
            //'js/lib/*.js',//先打包类库
            src + 'admin/js/**/*.js'
        ])
        .pipe(plumber())
        .pipe(babel({
          presets: ['es2015']
        }))
        .pipe(concat('main.js'))//合并js
        .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
        .pipe(uglify())
        .pipe(gulp.dest(dist + '/admin/js'));
});

// 编译模块js 并压缩不合并
gulp.task('pc_js', function() {
    gulp.src([
          //'js/lib/*.js',//先打包类库
          src + 'pc/js/**/*.js'
      ])
      .pipe(plumber())
      .pipe(babel({
        presets: ['es2015']
      }))
      .pipe(concat('main.js'))//合并js
      .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
      .pipe(uglify())
      .pipe(gulp.dest(dist + '/pc/js'));
});

// 编译模块js 并压缩不合并
gulp.task('diy_js', function() {
    gulp.src([
          src + 'diy/**/*.js'
      ])
      .pipe(plumber())
      .pipe(babel({
        presets: ['es2015']
      }))
      .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
      .pipe(uglify())
      .pipe(gulp.dest(dist + '/diy'));
});

// 图片文件直接copy
gulp.task('images', function () {
    gulp.src(src + 'images/**/*')
    .pipe(copy())
    .pipe(gulp.dest(dist + '/images'));
});

// 第三方资源库不编译的直接copy
gulp.task('lib', function () {
    gulp.src(src + 'lib/**/*')
    .pipe(copy())
    .pipe(gulp.dest(dist + '/lib'));
});

// 自动刷新

gulp.task('server', function() {
    /*
    browserSync.init({
        proxy: "muu.t5.com", // 指定代理url
        notify: false, // 刷新不弹出提示
    });
    */
    // 监听scss文件编译
    gulp.watch(src + 'admin/css/**/*.scss', ['admin_scss']);
    gulp.watch(src + 'pc/css/**/*.scss', ['pc_scss']);
    gulp.watch(src + 'diy/**/*.scss', ['diy_scss']);
    // 监听js文件编译
    gulp.watch(src + 'admin/js/**/*.js', ['admin_js']);
    gulp.watch(src + 'pc/js/**/*.js', ['pc_js']);
    gulp.watch(src + 'diy/**/*.js', ['diy_js']);
    // 监听其他不编译的文件 有变化直接copy
    gulp.watch(src + 'images/**/*.!(jpg|jpeg|png|gif|bmp|svg)', ['images']);
    gulp.watch(src + 'lib/**/*.!(jpg|jpeg|png|gif|bmp|svg|css|js)', ['lib']);   
    // 监听css文件变化后刷新页面
    gulp.watch(src + 'css/*.css').on("change", reload);
});

// 监听事件
gulp.task('default', ['admin_scss','pc_scss', 'diy_scss', 'admin_js', 'pc_js', 'diy_js', 'images', 'lib', 'server'])