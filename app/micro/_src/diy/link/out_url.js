$(function(){
    //触发的元素
    //初始化列表页码
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="out_url"]',function(){

        // 打开模态框
        $('#linkConfigModal').modal('show');
        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');
        // 构建LinkConfigModel内DOM结构
        var html_str="";
            html_str += '\<div class="form-horizontal">\
                <div class="form-group">\
                    <label class="col-sm-2">链接标题</label>\
                    <div class="col-sm-10">\
                        <input type="text" class="form-control" data-rule="data-link-title" placeholder="链接标题">\
                    </div>\
                </div>\
                <div class="form-group">\
                    <label class="col-sm-2">链接URL</label>\
                    <div class="col-sm-10">\
                        <input type="text" class="form-control"  data-rule="data-link-url" placeholder="http://">\
                    </div>\
                </div>\
            </div>';

        $('#linkConfigModal .modal-body').html(html_str);

        // 确认按钮点击选择事件
        $('#linkConfigModal').on('click','button.submit',function(){
            
            var title = $('#linkConfigModal [data-rule="data-link-title"]').val();
            var url = $('#linkConfigModal [data-rule="data-link-url"]').val();
            //DIY页面数据返回
            window.linkEelment.find('input[name="link_title"]').val(title);
            window.linkEelment.find('input[name="link_type"]').val('out_url');
            window.linkEelment.find('input[name="link_type_title"]').val('自定义链接');
            window.linkEelment.find('input[name="link_url"]').val(url);
            //element.find('[name="link_param"]').val(JSON.stringify(param));

            //按钮右侧链接文字
            window.linkEelment.find('.link_title li:eq(0)').html('自定义链接');
            window.linkEelment.find('.link_title li:eq(1)').html(title);
            
            // 关闭类型选择模态框
            $('#linkConfigModal').modal('hide');
        });
    });

});