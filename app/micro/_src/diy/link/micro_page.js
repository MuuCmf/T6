$(function(){
    //列表数据接口
    var api = '';
    //初始化列表页码
    var page = 1;
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="micro_page"]',function(){

        api = $(this).data('api');

        // 打开模态框
        $('#linkConfigModal').modal('show');
        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');
        // 清空dom
        $('#linkConfigModal .modal-body').html();
        var base_html = '\<div class="link-section">\
                        </div>\
                        <div class="link-page" style="text-align: center">\
                            <ul class="pager" data-ride="pager" data-elements="prev,nav,next"></ul>\
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
    $('body').on('click','#linkConfigModal [data-link-type="micro_page"]',function(){
        var data = {};
            data.link_title = $(this).data('link-title');
            //获取link_id
            data.link_id = $(this).data('link-id');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //web端链接地址
            data.link_url = $(this).data('link-url');
            //链接参数名
        var param = {
            id: $(this).data('link-id'),
            title: $(this).data('link-title')
        };
        //console.log(param);
        //返回数据有两种情况，1：DIY页面 2：底部导航设置页面
        //两者的dom结构不同需要单独处理
        //var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
            
        //DIY页面数据返回
        window.linkEelment.find('input[name="link_title"]').val(data.link_title);
        window.linkEelment.find('input[name="link_type"]').val(data.link_type);
        window.linkEelment.find('input[name="link_type_title"]').val(data.link_type_title);
        window.linkEelment.find('input[name="link_url"]').val(data.link_url);
        window.linkEelment.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        window.linkEelment.find('.link_title li:eq(0)').html(data.link_type_title);
        window.linkEelment.find('.link_title li:eq(1)').html(data.link_title);
        
        // 关闭类型选择模态框
        $('#linkConfigModal').modal('hide');
    });

    var getList = function(api, page){
        page = page || 1;
        api = api + '?r=5&page=' + page;
        $.get(api,function(res){
            console.log(res);
            var html_str = '';
            
            html_str += '<table class="table">';
            html_str +='<theader>';
            html_str +='<tr>';
            html_str +='<th>ID</th><th>标题</th><th>端</th><th>更新时间</th>';
            html_str +='</tr>';
            html_str +='</theader>';
            html_str +='<tbody>';
            $.each(res.data.data,function(i,n){
                html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type="micro_page" data-link-type-title="自定义页面">';
                html_str += '<td>'+n.id+'</td>';
                html_str += '<td>'+n.title+'</td>';
                html_str += '<td>'+n.port_type+'</td>';
                html_str += '<td>'+n.update_time_str+'</td>';
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