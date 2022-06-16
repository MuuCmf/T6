$(function(){
    //列表数据接口
    var api = '';
    //分类接口
    var category_api = '';

    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="articles_list"]',function(){
        
        api = $(this).data('api');
        category_api = $(this).data('category-api');
        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');
        // 清空dom
        $('#linkConfigModal .modal-body').html('');
        
        // 写入DOM
        var html_str = '';
        //获取分类接口数据
        //console.log(category_api);
        $.get(category_api,function(data){
            html_str += '<div class="form-horizontal articles-list">';
            html_str += '<div class="form-group">';
            html_str += '<label class="col-sm-2 control-label">按分类选择</label>';
            html_str += '<div class="col-md-6 col-sm-10">';
            html_str += '<select class="form-control" name="category_id">';      
            html_str += '<option value="0" selected>全部分类</option>';
   
            //console.log(data);
            if(data.data.length > 0){
                $.each(data.data,function(i,n){
                    html_str += '<option value="'+ n.id+'" style="font-weight:600">'+ n.title+'</option>';
                    if(typeof(n._child) != 'undefined'){
                        $.each(n._child,function(l,m){
                            html_str += '<option value="'+m.id+'">----'+m.title+'</option>';
                        })
                    }
                });
            }

            html_str += '</select>';
            html_str += '</div>';
            html_str += '</div>';

            html_str += '\
                <div class="form-group">\
                    <label class="col-sm-2 control-label">排序值</label>\
                    <div class="col-md-6 col-sm-10">\
                        <select class="form-control" name="order_field">\
                            <option value="create_time" selected="">发布时间</option>\
                            <option value="update_time">更新时间</option>\
                            <option value="view">浏览量</option>\
                        </select>\
                    </div>\
                </div>\
                <div class="form-group">\
                    <label class="col-sm-2 control-label">排序方式</label>\
                    <div class="col-md-6 col-sm-10">\
                        <select class="form-control" name="order_type">\
                            <option value="desc" selected="">按降序排列</option>\
                            <option value="asc">按升序排列</option>\
                        </select>\
                    </div>\
                </div>';
            html_str += '</div>';

            $('#linkConfigModal .modal-body').html(html_str);
            // 打开模态框
            $('#linkConfigModal').modal('show');
        });

        // 确认按钮点击选择事件
        $('#linkConfigModal').on('click','button.submit',function(){
            
            //获取link_cagegory_id
            var category_id = $('#linkConfigModal select[name="category_id"]').val();
            var category_title = $('#linkConfigModal select[name="category_id"] option:selected').text();
            var order_field = $('#linkConfigModal [name="order_field"]').val();
            var order_type = $('#linkConfigModal [name="order_type"]').val();
            var param = {
                category_id: category_id,
                order_field: order_field, 
                order_type: order_type
            };
            //DIY页面数据返回
            window.linkEelment.find('input[name="link_app"]').val('articles');
            window.linkEelment.find('input[name="link_title"]').val(category_title);
            window.linkEelment.find('input[name="link_type"]').val('articles_list');
            window.linkEelment.find('input[name="link_type_title"]').val('文章列表');
            window.linkEelment.find('[name="link_param"]').val(JSON.stringify(param));

            //按钮右侧链接文字
            window.linkEelment.find('.link_title li:eq(0)').html('文章列表');
            window.linkEelment.find('.link_title li:eq(1)').html(category_title);
            
            // 关闭类型选择模态框
            $('#linkConfigModal').modal('hide');
        });
    });

    
});