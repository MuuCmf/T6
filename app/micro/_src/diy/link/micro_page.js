$(function(){
    var link_type;
    //初始化组件索引
	var object_index;
	//初始化链接内容区索引
	var link_index = 0;
    //触发的元素
    var element;
    //初始化列表页码
    var page = 1;
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="micro_page"]',function(){
        link_type = $(this).data();
        object_index = $('#objectIndex').val();
        link_index = $('#linkIndex').val();
        element = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
        //console.log(object_index)
        //console.log(link_index)
        //console.log(link_type)
        // 打开模态框
        $('#linkConfigModal').modal('show');
        // 关闭类型选择模态框
        $('#linkTypeModal').modal('hide');

        // 请求数据
        var api = $(this).data('api');
            api = api + '?rows=5&page=' + page;
        var html_str="";
        //showLoading()
        $.get(api,function(res){
            console.log(res);
            html_str += '<table class="table">';
            html_str +='<theader>';
            html_str +='<tr>';
            html_str +='<th>ID</th><th>标题</th><th>端</th><th>更新时间</th>';
            html_str +='</tr>';
            html_str +='</theader>';
            html_str +='<tbody>';
            $.each(res.data.data,function(i,n){
                html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+link_type.linkType+' data-link-type-title='+link_type.linkTypeTitle+' data-link-module='+link_type.module+' data-link-url='+link_type.url+' data-link-param='+link_type.param+'>';
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
            //hideLoading();
        });
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
        element.find('input[name="link_title"]').val(data.link_title);
        element.find('input[name="link_type"]').val(data.link_type);
        element.find('input[name="link_type_title"]').val(data.link_type_title);
        element.find('input[name="link_url"]').val(data.link_url);
        element.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        element.find('.link_title li:eq(0)').html(data.link_type_title);
        element.find('.link_title li:eq(1)').html(data.link_title);
        
        // 关闭类型选择模态框
        $('#linkConfigModal').modal('hide');
    });
    
});