 /* ======================================================================== 
 * Copyright (c) 2014-2016 muucmf.cn; Licensed MIT
 /* ======================================================================== */

 (function($, window, undefined) {
    'use strict';

    /* Check jquery */
    if(typeof($) === 'undefined') throw new Error('muu requires jQuery');
    // muu shared object
    if(!$.muu) $.muu = function(obj) {
        if($.isPlainObject(obj)) {
            $.extend($.muu, obj);
        }
    };

    /**
     * 操纵toastor的便捷类
     * @type {{success: success, error: error, info: info, warning: warning}}
     */
     window.toast = {
        /**
        * 成功提示
        * @param text 内容
        * @param title 标题
        */
         success: function (text) {
             toast.show(text, {placement: 'center', type: 'success',close: true});
         },
         /**
          * 失败提示
          * @param text 内容
          * @param title 标题
          */
         error: function (text) {
             toast.show(text, {placement: 'center', type: 'danger',close: true});
         },
         /**
          * 信息提示
          * @param text 内容
          * @param title 标题
          */
         info: function (text) {
             toast.show(text, {placement: 'center', type: 'info',close: true});
         },
         /**
          * 警告提示
          * @param text 内容
          * @param title 标题
          */
         warning: function (text, title) {
             toast.show(text, {placement: 'center',type:'warning',close: true});
         },
 
         show: function (text, option) {
             new $.zui.Messager(text, option).show();
         },
         /**
          *  显示loading
          * @param text
          */
         showLoading: function () {
            var loader = '<div class="big-loading"><div class="loader"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div>';
            $('body').append(loader);
         },
         /**
          * 隐藏loading
          * @param text
          */
         hideLoading: function () {
             $('div').remove('.big-loading');
         }
     }
}(jQuery, window, undefined));



