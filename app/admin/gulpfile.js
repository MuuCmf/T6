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
    htmlmin     = require('gulp-htmlmin'),
    reload      = browserSync.reload;
    
// 定义源代码的目录和编译压缩后的目录
var src='./_src/',
    dist='../../public/static/admin';

// 编译app全部scss 并压缩
gulp.task('admin_scss', function(){
    gulp.src([src + 'css/**/*.scss'])
        .pipe(sass())
        .pipe(concat('main.css'))//合并css
        .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
        .pipe(minifyCss())
        .pipe(gulp.dest(dist+'/css'));
        //console.log(gulp.src(src+'/**/css/**/*.scss'));
});
// 编译核心adminlte_js 并压缩、合并
gulp.task('adminlte_js', function() {
  gulp.src(src + 'js/adminlte/*.js')
    .pipe(plumber())
    .pipe(babel({
      presets: ['es2015']
    }))
    .pipe(concat('adminlte.js'))//合并js
    .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
    .pipe(uglify())
    .pipe(gulp.dest(dist+'/js'));
});
// 编译核心js 并压缩、合并
gulp.task('main_js', function() {
    gulp.src(src + 'js/main/*.js')
    .pipe(plumber())
    .pipe(babel({
      presets: ['es2015']
    }))
    .pipe(concat('main.js'))//合并js
    .pipe(rename({suffix: '.min'}))//rename压缩后的文件名
    .pipe(uglify())
    .pipe(gulp.dest(dist+'/js'));

});
// CSS文件直接copy
gulp.task('css', function () {
    gulp.src(src + 'css/**/*.css')
    .pipe(copy())
    .pipe(gulp.dest(dist+'/css'));
});
// 图片文件直接copy
gulp.task('images', function () {
    gulp.src(src + 'images/**/*')
    .pipe(copy())
    .pipe(gulp.dest(dist+'/images'));
});
// 第三方资源库不编译的直接copy
gulp.task('lib', function () {
    gulp.src(src + 'lib/**/*')
    .pipe(copy())
    .pipe(gulp.dest(dist+'/lib'));
});

//压缩web html
//gulp.task('html',function(){
//    gulp.src([
//        src + '/html/**/*.html',
//    ])
//   .pipe(htmlmin({
        //collapseWhitespace:true,  //清除空格，压缩html，这一条比较重要，作用比较大，引起的改变压缩量也特别大。
        //collapseBooleanAttributes:true,  //省略布尔属性的值，比如：<input checked="checked"/>,那么设置这个属性后，就会变成 <input checked/>。
        //removeComments:true,  //清除html中注释的部分，我们应该减少html页面中的注释。
        //removeEmptyAttributes:true,  //清除所有的空属性。
        //removeScriptTypeAttributes:true,  //清除所有script标签中的type="text/javascript"属性。
        //removeStyleLinkTypeAttributes:true,  //清楚所有Link标签上的type属性。
        //minifyJS:true,  //压缩html中的javascript代码。
        //minifyCSS:true  //压缩html中的css代码。
//    }))
//   .pipe(gulp.dest('../view'));
//});

// 自动刷新
gulp.task('server', function() {
    /*
    browserSync.init({
        proxy: "www.a.com", // 指定代理url
        notify: false, // 刷新不弹出提示
    });
    */
    // 监听scss文件编译
    gulp.watch([src + 'css/**/*.scss'], ['admin_scss']);
    // 监听其他不编译的文件 有变化直接copy
    gulp.watch(src + 'images/**/*.!(jpg|jpeg|png|gif|bmp|svg)', ['images']);
    gulp.watch(src + 'lib/**/*.!(jpg|jpeg|png|gif|bmp|svg|css|js)', ['lib']);   
    // 监听核心js文件变化后刷新页面
    gulp.watch(src + 'js/adminlte/*.js', ['adminlte_js']).on("change", reload);
    gulp.watch(src + 'js/main/*.js', ['main_js']).on("change", reload);
    // 监听css文件变化后刷新页面
    gulp.watch(src + 'css/*.css').on("change", reload);
    // 监听html变化
    //gulp.watch(src + '/html/**/*.html', ['html']);
});
// 监听事件
gulp.task('default', ['admin_scss', 'adminlte_js', 'main_js', 'css','images', 'lib', 'server'])