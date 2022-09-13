
//手机号替换
$('#item').each(function () {
    var text = $(this).text();
    var array = text.split('');
    var replacement = array.splice(3, 4, "****");
    var conversion = array.toString();
    var mobile = conversion.replace(/,/ig, '');
    $(this).text(mobile);
})