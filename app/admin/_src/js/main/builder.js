var admin_image ={
    /**
     *
     * @param obj
     * @param attach
     */
    removeImage: function (obj, attach) {
        // 移除附件ID数据
        this.upAttachVal('del', attach, obj);
        obj.parents('.each').remove();

    },
    /**
     * 更新附件表单值
     * @return void
     */
    upAttachVal: function (type, attach,obj) {
        var $attachs = obj.parents('.controls').find('.attach');
        var attachVal = $attachs.val();
        var attachArr = attachVal.split(',');
        var newArr = [];
        for (var i in attachArr) {
            if (attachArr[i] !== '' && attachArr[i] !== attach.toString()) {
                newArr.push(attachArr[i]);
            }
        }
        type === 'add' && newArr.push(attach);
        $attachs.val(newArr.join(','));
        return newArr;
    }
}

//dom加载完成后执行的js
;$(function () {

    //全选的实现
    $(".check-all").click(function () {
        $(".ids").prop("checked", this.checked);
    });
    $(".ids").click(function () {
        var option = $(".ids");
        option.each(function (i) {
            if (!this.checked) {
                $(".check-all").prop("checked", false);
                return false;
            } else {
                $(".check-all").prop("checked", true);
            }
        });
    });

    // 独立域表单获取焦点样式
    $(".text").focus(function () {
        $(this).addClass("focus");
    }).blur(function () {
        $(this).removeClass('focus');
    });
    $("textarea").focus(function () {
        $(this).closest(".textarea").addClass("focus");
    }).blur(function () {
        $(this).closest(".textarea").removeClass("focus");
    });
});