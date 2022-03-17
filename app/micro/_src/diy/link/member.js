$(function(){
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="member"]',function(){
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

        window.linkEelment.find('input[name="link_title"]').val(data.link_title);
        window.linkEelment.find('input[name="link_type"]').val(data.link_type);
        window.linkEelment.find('input[name="link_type_title"]').val(data.link_type_title);
        window.linkEelment.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        window.linkEelment.find('.link_title li:eq(0)').html(data.link_type_title);
        window.linkEelment.find('.link_title li:eq(1)').html(data.link_title);
    });

});