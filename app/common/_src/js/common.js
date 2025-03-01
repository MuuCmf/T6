/**
 * 绑定回到顶部
 */
$(function () {
    $(window).on('scroll', function () {
        var st = $(document).scrollTop();
        if (st > 0) {
            $('#go-top').css('display', 'block');
        } else {
            $('#go-top').hide();
        }
    });
    $('#tool .go-top').on('click', function () {
        $('html,body').animate({ 'scrollTop': 0 }, 500);
    });

    $('#go-top .uc-2vm').hover(function () {
        $('#go-top .uc-2vm-pop').removeClass('dn');
    }, function () {
        $('#go-top .uc-2vm-pop').addClass('dn');
    });
});

/**
 * 绑定登出事件
 */
$(function () {
    $('[event-node=logout]').click(function () {
        var url = $(this).data('url');
        $.get(url, function (msg) {
            toast.success(msg.msg + '。', '温馨提示');
            setTimeout(function () {
                location.href = msg.url;
            }, 1500);
        }, 'json')
    });
})

var manage_image = {
    /**
     *
     * @param obj
     * @param attach
     */
    removeImage: function (obj, attach) {
        // 移除附件数据
        this.upAttachVal('del', attach, obj);
        obj.parents('.each').remove();

    },
    /**
     * 更新附件表单值
     * @return void
     */
    upAttachVal: function (type, attach, obj) {
        var $attachs = obj.parents('.controls').find('input.attach');
        var attachVal = $attachs.val();
        //console.log(attachVal);
        if(attachVal === '' || attachVal === undefined){
            return;
        }
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


