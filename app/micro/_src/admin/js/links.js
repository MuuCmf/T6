
/**
连接至JS
**/
$(function(){

	// 初始化组件索引
	var object_index;
	// 初始化链接内容区索引
	var link_index = 0;
	// 初始化页面类型 默认类型有 mobile pc tabbar announce
    var page_type;
    // 初始化触发的元素
    var element;

    // 打开连接至按钮
    $('body').on('click','[data-target="#linkTypeModal"]',function(){
        // 获取页面类型
        page_type = $(this).parents('#pageType').data('page-type');
        
        if(page_type == 'tabbar'){
            link_index = $('.footer-content').find('.footer-nav-form-item').index($(this).closest('.footer-nav-form-item'));
            element = $('.footer-content .footer-nav-form-item:eq('+link_index+')');
        }
        if(page_type == 'mobile'){
            // 获取组件索引
            object_index = $(this).parents('.object-item').attr('data-object-index');
            // 获取组件内元素
            link_index = $('[data-object-index="'+object_index+'"]').find('[data-rule="object-controller-item"]').index($(this).closest('[data-rule="object-controller-item"]'));
            element = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
        }

        if(page_type == 'pc'){

        }

        if(page_type == 'announce'){
            // 获取组件索引
            
        }

        // 赋值给全局变量
        window.linkEelment = element;
        // 赋值到模态框
        $('#linkTypeModal').find('#objectIndex').val(object_index);
        $('#linkTypeModal').find('#linkIndex').val(link_index);
    });

    
    
    
});