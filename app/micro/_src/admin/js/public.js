$(function(){
    
});

$(function(){
    //console.log('main-sidebar');
    $('.main-sidebar').scroll(function(){
        //console.log($(this).scrollTop());
        var scroll_top = $(this).scrollTop();
        localStorage.setItem("main-sidebar-scrolltop", scroll_top);
    });
});

$(function(){
    // 设置scrollTop
    var scroll_top = localStorage.getItem("main-sidebar-scrolltop");
    $('.main-sidebar').scrollTop(scroll_top);
    $('.main-sidebar').animate({opacity: 1 }, 3);
});