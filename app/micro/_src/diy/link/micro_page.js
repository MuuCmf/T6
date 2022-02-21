$(function(){


    // 点击自定义页面按钮
    $('body').on('click','[data-link-type="micro_page"]',function(){
            var api = $(this).data('api');
                api = api + '?rows=5&page=' + page;
            var html_str="";
            showLoading()
            $.get(api,function(data){

                html_str += '<table class="table">';
                html_str +='<theader>';
                html_str +='<tr>';
                html_str +='<th>ID</th><th>标题</th><th>端</th><th>更新时间</th>';
                html_str +='</tr>';
                html_str +='</theader>';
                html_str +='<tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+n.id+'</td>';
                    html_str += '<td>'+n.title+'</td>';
                    html_str += '<td>'+n.port_type+'</td>';
                    html_str += '<td>'+n.update_time_str+'</td>';
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#linkToModal .link-section').html(html_str); 
                // 获取分页器实例对象
                var diyPager = $('#linkToModal .pager').data('zui.pager');
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                hideLoading();
            });
    });
    
});