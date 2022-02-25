$(function(){
    var link_type;
    //初始化组件索引
	var object_index;
	//初始化链接内容区索引
	var link_index = 0;
    //触发的元素
    var element;
    //端类型
    var port_type;
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="member"]',function(){
        link_type = $(this).data();
        object_index = $('#objectIndex').val();
        link_index = $('#linkIndex').val();
        port_type = $('#portType').val();
        element = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
        //DIY页面数据返回
        if(port_type == 'mobile'){
            element = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
        }
        //底部导航
        if(port_type == 'tabbar'){
            //底部导航设置页数据返回
            element = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');

        var data = {};
            data.link_title = $(this).data('link-type-title');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //链接参数名
        var param = {
            title: $(this).data('link-type-title')
        };

        element.find('input[name="link_sys_type"]').val('direct');
        element.find('input[name="link_title"]').val(data.link_title);
        element.find('input[name="link_type"]').val(data.link_type);
        element.find('input[name="link_type_title"]').val(data.link_type_title);
        element.find('input[name="link_module"]').val(data.link_module);
        element.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        element.find('.link_title li:eq(0)').html(data.link_type_title);
        element.find('.link_title li:eq(1)').html(data.link_title);
    });

});