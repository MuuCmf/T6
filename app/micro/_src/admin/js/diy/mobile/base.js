
/* ======================================================================== 
 * Diy页面js
 * http://muucmf.cn
 * ======================================================================== 
 * Copyright (c) 2014-2016 muucmf.cn; Licensed MIT
 /* ======================================================================== */

(function($, window, undefined) {
    'use strict';

    /* Check jquery */
    if(typeof($) === 'undefined') throw new Error('muuDiy requires jQuery');
    // muu shared object
    if(!$.muuDiy) $.muuDiy = function(obj) {
        if($.isPlainObject(obj)) {
            $.extend($.muuDiy, obj);
        }
    };

    let header = function(){
        //点击显示头部控制区
        $('.page-diy-content').on("click",'.page-header .config-preview',function(){
            //已经显示的不再触发
            if($(this).find('.config-controller').hasClass('show')){
                return;
            }else{
                $('.object-item').find('.diy-preview-controller').removeClass('show');
                $(this).find('.config-controller').addClass('show');
            }
        });

        //标题框数据双向绑定
        
        $('.page-header').on('input propertychange','.config-controller input[name="title"]',function(){
            $('.page-header .config-preview .page-title').html($(this).val());
        });
    }

    let footer = function(){
        //点击显示头部控制区
        $('.page-diy-content').on("click",'.page-footer',function(e){
            console.log('底部管理配置');
            //已经显示的不再触发
            if($(this).find('.config-controller').hasClass('show')){
                return;
            }else{
                $('.object-item').find('.diy-preview-controller').removeClass('show');
                $(this).find('.config-controller').addClass('show');
            }
        });
    }
    

    //启用排序
    
    let sortable = function(options){
        // 拖放排序
        if($('.page-diy-section').length > 0){
            // 定义选项对象
            let options_s = {
                stopPropagation: true,
                selector: '.page-diy-section .object-item',
                finish: function(e) {
                    //$('.preview-target').sortable('destroy');
                },
            };
            $.extend(options, options_s);
            //调用排序
            $('.page-diy-section .preview-target').sortable(options);
            // 解除文本框默认事件禁用
            $(".preview-target").on("mousedown",'.diy-preview-controller input',function(e) {
                e.stopPropagation();
                $(this).focus();
            });
            $(".preview-target").on("mousedown",'.diy-preview-controller select',function(e) {
                e.stopPropagation();
                $(this).focus();
            });
        }
        
    };
    
    //关闭头尾部控制区
    let close_header_fooder = function(){
        $('.page-diy-section').on("click",'.object-item',function(){
            $('.page-header .config-controller').removeClass('show');
            $('.page-footer .config-controller').removeClass('show');
        });
    }
    
    /**
     * 移除dom
     *
     * @param      {<type>}  ele     The ele
     */
    let remove_object = function(ele){
        //点击删除按钮移除组件DOM
        $('.page-diy-section').on("click",'.del-btn',function(){
            $(this).parents('.object-item').animate({opacity:0},260,'swing',function(){
                $(this).remove();
            });
            //$(this).parents('.object-item').remove();
        });
    }

    /**
     * 向上移动一层DOM
     *
     * @param      {<type>}  ele     The ele
     */
    let up_object = function(){
        $('.page-diy-section').on("click",'.up-btn',function(event){
            event.stopPropagation();
            //隐藏控制区,重新触发获取索引
            $(this).siblings('.diy-preview-controller').removeClass('show');
            //
            let p_ele = $(this).parents('.object-item');
            let ele = $(p_ele).prevAll('.object-item')[0];
            let html = $(this).parents('.object-item').prop("outerHTML");
            //console.log(ele);
            //console.log(html);

            //移除
            $(this).parents('.object-item').animate({opacity:0},260,'swing',function(){
                $(this).remove();
            });
            //$(this).parents('.object-item').remove();

            //新增
            $(ele).animate({opacity:100},260,'swing',function(){
                $(this).before(html);;
            });
        });
    }
   
    /**
     * 向下移动一层DOM
     *
     * @param      {<type>}  ele     The ele
     */
    let down_object = function(){
        $('.page-diy-section').on("click",'.down-btn',function(event){
            event.stopPropagation();
            //隐藏控制区,重新触发获取索引
            $(this).siblings('.diy-preview-controller').removeClass('show');
            
            let p_ele = $(this).parents('.object-item');
            let ele = $(p_ele).nextAll('.object-item')[0];
            let html = $(this).parents('.object-item').prop("outerHTML");

            $(this).parents('.object-item').animate({opacity:0},260,'swing',function(){
                $(this).remove();
            });
            $(ele).animate({opacity:100},260,'swing',function(){
                $(this).after(html);
            });

        });
    }

    $(document).ready(function(){
        //执行
        header();
        footer();
        sortable();
        remove_object();
        close_header_fooder();
        up_object();
        down_object();
    });

}(jQuery, window, undefined));