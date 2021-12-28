 // 首页顶部背景
 if (window.location.pathname == "/muu/index/index.html") {
    $(window).scroll(function () {
        // console.log(222222)
        if ($(window).scrollTop() >= 750) {
            $(".header-container").css({
                "background": "#fff"
            });
        } else {
            $(".header-container").css({
                "background": "transparent"
            });
        }
    })
    if($(window).scrollTop() >= 750){
        $(".header-container").css({
            "background": "#fff"
        });
    }
} else {
    $(".header-container").css({
        "background": "#fff"
    });
}
//下划线
// ----------------------------
if (window.location.pathname != "/muu/index/index.html" &&
    window.location.pathname != "/muu/frame/index.html" &&
    window.location.pathname != "/muu/nslookup/index.html" &&
    window.location.pathname != "/muu/about/index.html" &&
    window.location.pathname != "/ucenter/Common/agreement.html") {
    $(".product").css({
        "color": "#358cc2"
    });
    $(".product-underline").css({
        'display': 'block'
    });
}
//---------------------------
//手机号替换
$('#item').each(function () {
    var text = $(this).text();
    var array = text.split('');
    var replacement = array.splice(3, 4, "****");
    var conversion = array.toString();
    var mobile = conversion.replace(/,/ig, '');
    $(this).text(mobile);
})