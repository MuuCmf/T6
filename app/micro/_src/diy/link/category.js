$(function(){
    var link_type;
    //初始化组件索引
	var object_index;
	//初始化链接内容区索引
	var link_index = 0;
    //触发的元素
    var element;
    //列表数据接口
    var api = '';
    //初始化列表页码
    var page = 1;
    // 打开连接至设置模特框
    $('body').on('click','#linkTypeModal [data-link-type="category"]',function(){
        link_type = $(this).data();
        object_index = $('#objectIndex').val();
        link_index = $('#linkIndex').val();
        element = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
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
                }
            }
        });
        
        getList(api, page);
    });

    // 列表点击选择事件
    $('body').on('click','#linkConfigModal [data-link-type="category"]',function(){
        var data = {};
            data.link_title = $(this).data('link-title');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //web端链接地址
            data.link_url = $(this).data('link-url');
            //链接参数名
        var param = {
            app: $(this).data('link-name'),
        };

        //DIY页面数据返回
        element.find('input[name="link_title"]').val(data.link_title);
        element.find('input[name="link_type"]').val(data.link_type);
        element.find('input[name="link_type_title"]').val(data.link_type_title);
        element.find('[name="link_param"]').val(JSON.stringify(param));
        //按钮右侧链接文字
        element.find('.link_title li:eq(0)').html(data.link_type_title);
        element.find('.link_title li:eq(1)').html(data.link_title);
        
        // 关闭类型选择模态框
        $('#linkConfigModal').modal('hide');
    });

    /**
     * 获取数据
     * @param {*} api 
     * @param {*} page 
     */
    var getList = function(api, page){
        page = page || 1;
        api = api + '?r=9&page=' + page;
        $.get(api,function(res){
            console.log(res);
            var html_str = '';
            
            html_str += '<div class="applist clearfix">';
            
            $.each(res.data.data,function(i,n){
                html_str += '<div class="item" data-rule="link_param" data-link-name='+n.name+' data-link-title='+n.alias+' data-link-type='+link_type.linkType+' data-link-type-title='+link_type.linkTypeTitle+'>';
                html_str += '<div class="icon"><img src="'+n.icon+'" /></div>';
                html_str += '<div class="alias">'+n.alias+'</div>';
                html_str += '</div>'; 
            });
            html_str += '</div>';

            $('#linkConfigModal .link-section').html(html_str);
            // 获取分页器实例对象
            var diyPager = $('#linkConfigModal .pager').data('zui.pager');
            //动态更新分页器
            diyPager.set(parseInt(res.data.current_page), parseInt(res.data.total), parseInt(res.data.per_page));
        });
    }
    
});