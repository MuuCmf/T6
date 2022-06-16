$(function(){
    //列表数据接口
    var api = '';
    //初始化列表页码
    var page = 1;
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="articles_detail"]',function(){

        api = $(this).data('api');

        // 打开模态框
        $('#linkConfigModal').modal('show');
        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');
        // 清空dom
        $('#linkConfigModal .modal-body').html('');
        var base_html = '\<div class="knowledge-detail">\
                        <div class="link-search">\
                            <div class="input-group">\
                            <div class="input-control search-box has-icon-left has-icon-right">\
                                <input type="hidden" name="api">\
                                <input id="inputSearch" type="search" class="form-control search-input empty" name="keyword" placeholder="搜索">\
                                <label for="inputSearch" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>\
                            </div>\
                            <span class="input-group-btn">\
                                <button class="btn btn-primary" type="button">搜索</button>\
                            </span>\
                        </div>\
                        </div>\
                        <div class="link-section">\
                        </div>\
                        <div class="link-page" style="text-align: center">\
                            <ul class="pager" data-ride="pager" data-elements="prev,nav,next"></ul>\
                        </div>\
                        </div>';
        $('#linkConfigModal .modal-body').html(base_html);
        // 手动进行初始化分页器
        $('.pager').pager({
            page: 1,
            lang: 'zh_cn',
            onPageChange: function(state, oldState) {
                if (state.page !== oldState.page) {
                    getList(api, state.page);
                    //console.log('页码从', oldState.page, '变更为', state.page);
                }
            }
        });
        
        getList(api, page);
        //hideLoading();
    });

    // 列表点击选择事件
    $('body').on('click','#linkConfigModal [data-link-type="articles_detail"]',function(){
        
        var link_title = $(this).data('link-title');
        //获取link_id
        var link_id = $(this).data('link-id');
        //获取link_type
        var link_type = $(this).data('link-type');
        //获取链接类型的标题
        var link_type_title = $(this).data('link-type-title');
        //链接参数名
        var param = {
            id: link_id,
            //title: $(this).data('link-title')
        };
            
        //DIY页面数据返回
        window.linkEelment.find('input[name="link_app"]').val('articles');
        window.linkEelment.find('input[name="link_title"]').val(link_title);
        window.linkEelment.find('input[name="link_type"]').val(link_type);
        window.linkEelment.find('input[name="link_type_title"]').val(link_type_title);
        window.linkEelment.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        window.linkEelment.find('.link_title li:eq(0)').html(link_type_title);
        window.linkEelment.find('.link_title li:eq(1)').html(link_title);
        
        // 关闭类型选择模态框
        $('#linkConfigModal').modal('hide');
    });

    var getList = function(api, page){
        page = page || 1;
        api = api + '?rows=5&status=1&page=' + page;
        $.get(api,function(res){
            console.log(res);
            var html_str = '';
                html_str += '<table class="table"><tbody>';
            $.each(res.data.data,function(i,n){
                html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type="articles_detail" data-link-type-title="文章详情">';
                html_str += '<td>';
                html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                
                html_str += '</div>'
                html_str += '</td>';
                html_str += '<td>文章</td>';
                html_str += '</tr>'; 
            });
            html_str += '</tbody></table>';

            $('#linkConfigModal .link-section').html(html_str);
            // 获取分页器实例对象
            var diyPager = $('#linkConfigModal .pager').data('zui.pager');
            //动态更新分页器
            diyPager.set(parseInt(res.data.current_page), parseInt(res.data.total), parseInt(res.data.per_page));
        });
    }
    
});