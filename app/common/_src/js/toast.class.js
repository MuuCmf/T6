/**
 * 操纵toastor的便捷类
 * @type {{success: success, error: error, info: info, warning: warning}}
 */
var toast = {
    /**
     * 成功提示
     * @param text 内容
     * @param title 标题
     */
    success: function (text, placement = 'center') {
        toast.show(text, { placement: placement, type: 'success', close: true });
    },
    /**
     * 失败提示
     * @param text 内容
     * @param title 标题
     */
    error: function (text, placement = 'center') {
        toast.show(text, { placement: placement, type: 'danger', close: true });
    },
    /**
     * 信息提示
     * @param text 内容
     * @param title 标题
     */
    info: function (text, placement = 'center') {
        toast.show(text, { placement: placement, type: 'info', close: true });
    },
    /**
     * 警告提示
     * @param text 内容
     * @param title 标题
     */
    warning: function (text, placement = 'center') {
        toast.show(text, { placement: placement, type: 'warning', close: true });
    },
    /**
     * 默认提示
     * @param {*} text 
     */
    default: function (text, placement = 'center') {
        toast.show(text, { placement: placement, type: 'default', close: true });
    },

    show: function (text, option) {
        var zui = $.zui;
        if (zui) {
            $.zui.messager.show(text, option);
        } else {
            $.messager.show(text, option);
        }
    },
    /**
     *  显示loading
     * @param text
     */
    showLoading: function () {
        $('body').append('<div class="big_loading"><img src="/static/common/images/big_loading.gif"/></div>');
    },
    /**
     * 隐藏loading
     * @param text
     */
    hideLoading: function () {
        $('div').remove('.big_loading');
    }
}