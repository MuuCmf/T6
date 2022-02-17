
/**
* 
连接至JS

思路：
三种情况：1、内部列表类链接 2 内部详情类链接 3、外部链接（已剥离出去）
链接至功能 可链接页面 整理
## 内部页
单课列表页 
单课详情页
专栏列表页
专栏详情页
讲师列表页 
讲师详情页
自定义页面
会员服务

## 外部页
外部页参数：1链接标题 2链接URL

* 
**/
$(function(){

	//初始化组件索引
	var object_index;
	//初始化链接内容区索引
	var link_index = 0;
	//初始化底部导航设置页链接区索引
    var footer_link_index = 0;
    //PC端导航设置链接区索引
    var pc_link_index = 0;
	//初始化组件数据
    var data_obj;
    //端 1：DIY页面 2：底部导航 3：PC端导航页
    var this_page;

    var category_api = '';

    // 打开连接至按钮
    $('body').on('click','[data-target="#linkTypeModal"]',function(){
        this_page = $(this).parents('.this-page').attr('data-this-page');
        object_index = $(this).parents('.object-item').attr('data-object-index');
        link_index = $('[data-object-index="'+object_index+'"]').find('[data-rule="object-controller-item"]').index($(this).closest('[data-rule="object-controller-item"]'));
        footer_link_index = $('.footer-content').find('.footer-nav-form-item').index($(this).closest('.footer-nav-form-item'));
        pc_link_index = $('.pc-nav-box').find('.pc-nav-form-item').index($(this).closest('.pc-nav-form-item'));
        //console.log(pc_link_index);
        //console.log(this_page);
    });

	// 内部链接详情类型（sys_type:detail）绑定链接至链接打开模态窗后的处理
	$('body').on('click','[data-target="#detailLinkModal"]',function(){
        $('#linkTypeModal .sr-only').click();
		//dom对象数据
		data_obj = $(this).data();
        //console.log(data_obj);
		//内部链接
		generatingTable(data_obj);
	});

    // 内部链接列表类型（sys_type:list）绑定链接至链接打开模态窗后的处理
    $('body').on('click','[data-target="#listLinkModal"]',function(){
        $('#linkTypeModal .sr-only').click();
        //dom对象数据
        data_obj = $(this).data();
        category_api = data_obj.categoryApi;
        console.log(data_obj);
        generatingTable(data_obj);
    });

    // 外部链接绑定链接至链接打开模态窗后的处理
    $('body').on('click','[data-target="#linkOutUrlModal"]',function(){
        $('#linkTypeModal .sr-only').click();
        //dom对象数据
        data_obj = $(this).data();
        //外部链接模态框打开后的处理
        //获取已经赋值的数据
        var title = $(this).parents('[data-rule="links_list"]').find('[name="link_title"]').val();
        var url = $(this).parents('[data-rule="links_list"]').find('[name="link_url"]').val();
        
        //赋值到模态框
        $('#linkOutUrlModal').find('[data-rule="data-link-title"]').val(title);
        $('#linkOutUrlModal').find('[data-rule="data-link-url"]').val(url);
    });

    //选择直链至会员付费方式点击事件
    $('#linkTypeModal').on('click','[data-link-type="member"]',function(){

        var data = {};
            data.link_title = $(this).data('link-type-title');
            //获取link_id
            data.link_id = $(this).data('link-id');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //获取链接所属模型
            data.link_module = $(this).data('link-module');
            //web端链接地址
            data.link_url = $(this).data('link-url');
            //链接参数名
        var param = {
            title: $(this).data('link-type-title')
        };
        //console.log(data);
        //返回数据有两种情况，1：DIY页面 2：底部导航设置页面
        //两者的dom结构不同需要单独处理
        
        //DIY页面数据返回
        if(this_page == 'page'){
            var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
            //部分组件需要写入并不在该DOM中的link_title
            $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')').find('input[name="link_title"]').val(data.link_title);
        }
        //底部导航
        if(this_page == 'footer'){
            //底部导航设置页数据返回
            var this_ele = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        //PC导航页
        if(this_page == 'pc'){
            //底部导航设置页数据返回
            var this_ele = $('.pc-nav-box .pc-nav-form-item:eq('+pc_link_index+')');
        }

        this_ele.find('input[name="link_sys_type"]').val('direct');
        this_ele.find('input[name="link_title"]').val(data.link_title);
        this_ele.find('input[name="link_type"]').val(data.link_type);
        this_ele.find('input[name="link_type_title"]').val(data.link_type_title);
        this_ele.find('input[name="link_module"]').val(data.link_module);
        this_ele.find('input[name="link_url"]').val(data.link_url);
        this_ele.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        this_ele.find('.link_title li:eq(0)').html(data.link_type_title);
        this_ele.find('.link_title li:eq(1)').html(data.link_title);
        
        //关闭模态框
        $('[data-dismiss="modal"]').click();
    });

    //选择直链至会员付费方式点击事件
    $('#linkTypeModal').on('click','[data-link-type="category"]',function(){

        var data = {};
            data.link_title = $(this).data('link-type-title');
            //获取link_id
            data.link_id = $(this).data('link-id');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //获取链接所属模型
            data.link_module = $(this).data('link-module');
            //web端链接地址
            data.link_url = $(this).data('link-url');
            //链接参数名
        var param = {
            title: $(this).data('link-type-title')
        };
        //console.log(data);
        //返回数据有两种情况，1：DIY页面 2：底部导航设置页面
        //两者的dom结构不同需要单独处理
        //var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
            
        //DIY页面数据返回
        if(this_page == 'page'){
            var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
            //部分组件需要写入并不在该DOM中的link_title
            $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')').find('input[name="link_title"]').val(data.link_title);
        }
        //底部导航
        if(this_page == 'footer'){
            //底部导航设置页数据返回
            var this_ele = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        //PC导航页
        if(this_page == 'pc'){
            //PC导航设置页数据返回
            var this_ele = $('.pc-nav-box .pc-nav-form-item:eq('+pc_link_index+')');
        }

        this_ele.find('input[name="link_sys_type"]').val('direct');
        this_ele.find('input[name="link_title"]').val(data.link_title);
        this_ele.find('input[name="link_type"]').val(data.link_type);
        this_ele.find('input[name="link_type_title"]').val(data.link_type_title);
        this_ele.find('input[name="link_module"]').val(data.link_module);
        this_ele.find('input[name="link_url"]').val(data.link_url);
        this_ele.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        this_ele.find('.link_title li:eq(0)').html(data.link_type_title);
        this_ele.find('.link_title li:eq(1)').html(data.link_title);
        
        //关闭模态框
        $('[data-dismiss="modal"]').click();
    });

    /**
     * 获取弹出模态框数据并写入DOM
     */
    var getModalData = function(api,linkType){
        var diyPager = $('#detailLinkModal .pager').data('zui.pager');
            //diyPager.set(1, 5, 5);

        if(linkType == 'column_detail'){

            $.get(api,function(data){
                var html_str="";
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>';
                    html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                    if(n.expense == 0){
                        html_str += '<div class="price">免费</div>';
                    }else{
                        html_str += '<div class="price">￥ '+ n.price +'</div>';
                    }
                    html_str += '</div>';
                    html_str += '</td>';
                    
                    if(n.status == 1){
                        html_str += '<td><span class="label label-info">'+ n.status_str +'</span></td>';
                    }else{
                        html_str += '<td><span class="label label-warning">'+ n.status_str +'</span></td>';
                    }
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                
                //hideLoading();
            });
        }
          
        if(linkType == 'knowledge_detail'){
            
            $.get(api,function(data){
                //console.log(data);
                var html_str = '';
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+ n.type_str +'</td>';
                    html_str += '<td>';
                    html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                    if(n.expense == 0){
                        html_str += '<div class="price">免费</div>';
                    }else{
                        html_str += '<div class="price">￥ '+ n.price +'</div>';
                    }
                    html_str += '</div>'
                    html_str += '</td>';
                    if(n.status == 1){
                        html_str += '<td><span class="label label-info">'+ n.status_str +'</span></td>';
                    }else{
                        html_str += '<td><span class="label label-warning">'+ n.status_str +'</span></td>';
                    }
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                //hideLoading();
            });
        }

        //线下课详情
        if(linkType == 'offline_detail'){
    
            $.get(api,function(data){
                //console.log(data);
                var html_str = '';
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+ n.type_str +'</td>';
                    html_str += '<td>';
                    html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                    if(n.expense == 0){
                        html_str += '<div class="price">免费</div>';
                    }else{
                        html_str += '<div class="price">￥ '+ n.price +'</div>';
                    }
                    html_str += '</div>'
                    html_str += '</td>';
                    if(n.status == 1){
                        html_str += '<td><span class="label label-info">'+ n.status_str +'</span></td>';
                    }else{
                        html_str += '<td><span class="label label-warning">'+ n.status_str +'</span></td>';
                    }
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                //hideLoading();
            });
        }

        //资料详情
        if(linkType == 'material_detail'){
            
            $.get(api,function(data){
                //console.log(data);
                var html_str = '';
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+ n.type_str +'</td>';
                    html_str += '<td>';
                    html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                    if(n.expense == 0){
                        html_str += '<div class="price">免费</div>';
                    }else{
                        html_str += '<div class="price">￥ '+ n.price +'</div>';
                    }
                    html_str += '</div>'
                    html_str += '</td>';
                    if(n.status == 1){
                        html_str += '<td><span class="label label-info">'+ n.status_str +'</span></td>';
                    }else{
                        html_str += '<td><span class="label label-warning">'+ n.status_str +'</span></td>';
                    }
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                //hideLoading();
            });
        }

        //试卷详情
        if(linkType == 'exam_paper_detail'){
            
            $.get(api,function(data){
                //console.log(data);
                var html_str = '';
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+ n.type_str +'</td>';
                    html_str += '<td>';
                    html_str += '<div class="cover"><img src="'+ n.cover_200 +'" /></div>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.title +'</div>';
                    if(n.expense == 0){
                        html_str += '<div class="price">免费</div>';
                    }else{
                        html_str += '<div class="price">￥ '+ n.price +'</div>';
                    }
                    html_str += '</div>'
                    html_str += '</td>';
                    if(n.status == 1){
                        html_str += '<td><span class="label label-info">'+ n.status_str +'</span></td>';
                    }else{
                        html_str += '<td><span class="label label-warning">'+ n.status_str +'</span></td>';
                    }
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                //hideLoading();
            });
        }

        // 跳转小程序列表页
        if(linkType == 'tominiprogram'){
            
            $.get(api,function(data){
                //console.log(data);
                var html_str = '';
                html_str += '<table class="table"><tbody>';
                $.each(data.data.data,function(i,n){
                    html_str += '<tr data-rule="link_param" data-link-id='+n.id+' data-link-title='+n.title+' data-link-type='+data_obj.linkType+' data-link-type-title='+data_obj.linkTypeTitle+' data-link-module='+data_obj.module+' data-link-url='+data_obj.url+' data-link-param='+data_obj.param+'>';
                    html_str += '<td>'+ n.title +'</td>';
                    html_str += '<td>';
                    html_str += '<div class="info"><div class="title text-ellipsis">'+ n.appid +'</div>';
                    html_str += '</div>'
                    html_str += '</td>';
                    html_str += '</tr>'; 
                });
                html_str += '</tbody></table>';

                $('#detailLinkModal .link-section').html(html_str);
                
                //动态更新分页器
                diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                //hideLoading();
            });
        }
    }

	/**
	 * 读取ajax数据并构建数据表格
	 *
	 * @param      {<type>}  data    The data
	 * @param      {<type>}  type    The type
	 */
	var generatingTable = function(data_obj,page){
        
        //showLoading();

        page = page || 1;

        //console.log(data_obj);

        var api = data_obj.api;
        //console.log(data_obj);
        //清空内容
        $("#listLinkModal .link-section").html('');
        $("#detailLinkModal .link-section").html('');
        //console.log(data_obj);
        //可根据data_obj.type给的不同类型构建不同html结构
        switch(data_obj.linkType){

            case 'column_detail': //专栏
                api = api + '?rows=5&page=' + page;
                //搜索功能
                $('#detailLinkModal .link-search input[name="api"]').val(api);
                $('#detailLinkModal .link-search').unbind("click").on('click','.btn',function(event){
                    event.preventDefault();
                    var keyword = $('#detailLinkModal .link-search input[name="keyword"]').val();
                    var api = $('#detailLinkModal .link-search input[name="api"]').val();
                    var api = api + '&keyword='+ keyword;
                    getModalData(api,'column_detail');
                });

                getModalData(api,'column_detail');

            break;

            case 'column_list': //专栏

                var  html_str = '';
                
                html_str += '<div class="form-horizontal">';
                $.get(category_api,function(data){
                    html_str += '\
                        <div class="form-group">\
                            <label class="col-sm-2">按分类选择</label>\
                            <div class="col-md-6 col-sm-10">\
                            <select class="form-control" name="category_id">';
                            
                            html_str += '<option value="" selected >全部分类</option>';
                            if(data.data.length > 0){
                                $.each(data.data,function(i,n){
                                    console.log(n);
                                    html_str += '<option value="'+ n.id+'" style="font-weight:600">'+ n.title+'</option>';
                                    if(typeof(n._child) != 'undefined'){
                                        $.each(n._child,function(l,m){
                                            html_str += '<option value="'+m.id+'">----'+m.title+'</option>';
                                        })
                                    }
                                });
                            }

                    html_str += '\</select>';
                    html_str += '\
                            </div>\
                        </div>';
                    
                    html_str += '\<div class="form-group">\
                            <label class="col-sm-2">排序值</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_field">\
                                    <option value="create_time" selected="">发布时间</option>\
                                    <option value="update_time">更新时间</option>\
                                    <option value="view">浏览量</option>\
                                </select>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="col-sm-2">排序方式</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_type">\
                                    <option value="desc" selected="">按降序排列</option>\
                                    <option value="asc">按升序排列</option>\
                                </select>\
                            </div>\
                        </div>';
                    html_str += '</div>';

                    $("#listLinkModal .link-section").html(html_str);

                    //hideLoading();
                });
                
            break;

            case 'knowledge_detail': //知识
                api = api + '?rows=5&page=' + page;
                //搜索功能
                $('#detailLinkModal .link-search input[name="api"]').val(api);
                $('#detailLinkModal .link-search').unbind("click").on('click','.btn',function(event){
                    event.preventDefault();
                    var keyword = $('#detailLinkModal .link-search input[name="keyword"]').val();
                    var api = $('#detailLinkModal .link-search input[name="api"]').val();
                    var api = api + '&alone_sale=1&keyword='+ keyword;
                    getModalData(api,'knowledge_detail');
                });

                getModalData(api,'knowledge_detail');

            break;

            case 'knowledge_list': //点播课列表

                var html_str = '';
                //获取分类接口数据
                //console.log(category_api);
                $.get(category_api,function(data){
                    html_str += '<div class="form-horizontal">';
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
                            <label class="col-sm-2 control-label">按类型选择</label>\
                            <div class="col-sm-10">\
                                <div class="select-item select-active" data-type="type" data-value="all" data-title="全部">全部</div>\
                                <div class="select-item" data-type="type" data-value="image_text" data-title="图文">图文</div>\
                                <div class="select-item" data-type="type" data-value="video" data-title="视频">视频</div>\
                                <div class="select-item" data-type="type" data-value="audio" data-title="音频">音频</div>\
                            </div>\
                        </div>\
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

                    $("#listLinkModal .link-section").html(html_str);
                    //隐藏遮罩
                    //hideLoading();
                });
            break;

            case 'material_list': //资料列表

                var  html_str = '';
                html_str += '<div class="form-horizontal">';
                $.get(category_api,function(data){
                    html_str += '\
                        <div class="form-group">\
                            <label class="col-sm-2">按分类选择</label>\
                            <div class="col-md-6 col-sm-10">\
                            <select class="form-control" name="category_id">';
                            
                            html_str += '<option value="" selected >全部分类</option>';
                            if(data.data.length > 0){
                                $.each(data.data,function(i,n){
                                    console.log(n);
                                    html_str += '<option value="'+ n.id+'" style="font-weight:600">'+ n.title+'</option>';
                                    if(typeof(n._child) != 'undefined'){
                                        $.each(n._child,function(l,m){
                                            html_str += '<option value="'+m.id+'">----'+m.title+'</option>';
                                        })
                                    }
                                });
                            }

                    html_str += '\</select>';
                    html_str += '\
                            </div>\
                        </div>';
                    
                    html_str += '\<div class="form-group">\
                            <label class="col-sm-2">排序值</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_field">\
                                    <option value="create_time" selected="">发布时间</option>\
                                    <option value="update_time">更新时间</option>\
                                    <option value="view">浏览量</option>\
                                </select>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="col-sm-2">排序方式</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_type">\
                                    <option value="desc" selected="">按降序排列</option>\
                                    <option value="asc">按升序排列</option>\
                                </select>\
                            </div>\
                        </div>';
                    html_str += '</div>';

                    $("#listLinkModal .link-section").html(html_str);

                    //hideLoading();
                });
                
            break;

            case 'material_detail': //资料详情
                api = api + '?rows=5&page=' + page;
                //搜索功能
                $('#detailLinkModal .link-search input[name="api"]').val(api);
                $('#detailLinkModal .link-search').unbind("click").on('click','.btn',function(event){
                    event.preventDefault();
                    var keyword = $('#detailLinkModal .link-search input[name="keyword"]').val();
                    var api = $('#detailLinkModal .link-search input[name="api"]').val();
                    var api = api + '&keyword='+ keyword;
                    getModalData(api,'material_detail');
                });

                getModalData(api,'material_detail');
            break;


            case 'offline_list': //线下课列表

                var  html_str = '';
                html_str += '<div class="form-horizontal">';
                $.get(category_api,function(data){
                    html_str += '\
                        <div class="form-group">\
                            <label class="col-sm-2">按分类选择</label>\
                            <div class="col-md-6 col-sm-10">\
                            <select class="form-control" name="category_id">';
                            
                            html_str += '<option value="" selected >全部分类</option>';
                            if(data.data.length > 0){
                                $.each(data.data,function(i,n){
                                    console.log(n);
                                    html_str += '<option value="'+ n.id+'" style="font-weight:600">'+ n.title+'</option>';
                                    if(typeof(n._child) != 'undefined'){
                                        $.each(n._child,function(l,m){
                                            html_str += '<option value="'+m.id+'">----'+m.title+'</option>';
                                        })
                                    }
                                });
                            }

                    html_str += '\</select>';
                    html_str += '\
                            </div>\
                        </div>';
                    
                    html_str += '\<div class="form-group">\
                            <label class="col-sm-2">排序值</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_field">\
                                    <option value="create_time" selected="">发布时间</option>\
                                    <option value="update_time">更新时间</option>\
                                    <option value="view">浏览量</option>\
                                </select>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="col-sm-2">排序方式</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_type">\
                                    <option value="desc" selected="">按降序排列</option>\
                                    <option value="asc">按升序排列</option>\
                                </select>\
                            </div>\
                        </div>';
                    html_str += '</div>';

                    $("#listLinkModal .link-section").html(html_str);
                });
                
            break;

            case 'offline_detail': //线下课详情
                api = api + '?rows=5&page=' + page;
                //搜索功能
                $('#detailLinkModal .link-search input[name="api"]').val(api);
                $('#detailLinkModal .link-search').unbind("click").on('click','.btn',function(event){
                    event.preventDefault();
                    var keyword = $('#detailLinkModal .link-search input[name="keyword"]').val();
                    var api = $('#detailLinkModal .link-search input[name="api"]').val();
                    var api = api + '&keyword='+ keyword;
                    getModalData(api,'offline_detail');
                });

                getModalData(api,'offline_detail');
            break;

            case 'exam_paper_list': //试卷列表

                var  html_str = '';
                html_str += '<div class="form-horizontal">';
                $.get(category_api,function(data){
                    html_str += '\
                        <div class="form-group">\
                            <label class="col-sm-2">按分类选择</label>\
                            <div class="col-md-6 col-sm-10">\
                            <select class="form-control" name="category_id">';
                            
                            html_str += '<option value="" selected >全部分类</option>';
                            if(data.data.length > 0){
                                $.each(data.data,function(i,n){
                                    console.log(n);
                                    html_str += '<option value="'+ n.id+'" style="font-weight:600">'+ n.title+'</option>';
                                    if(typeof(n._child) != 'undefined'){
                                        $.each(n._child,function(l,m){
                                            html_str += '<option value="'+m.id+'">----'+m.title+'</option>';
                                        })
                                    }
                                });
                            }

                    html_str += '\</select>';
                    html_str += '\
                            </div>\
                        </div>';
                    
                    html_str += '\<div class="form-group">\
                            <label class="col-sm-2">排序值</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_field">\
                                    <option value="create_time" selected="">发布时间</option>\
                                    <option value="update_time">更新时间</option>\
                                    <option value="view">浏览量</option>\
                                </select>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="col-sm-2">排序方式</label>\
                            <div class="col-md-6 col-sm-10">\
                                <select class="form-control" name="order_type">\
                                    <option value="desc" selected="">按降序排列</option>\
                                    <option value="asc">按升序排列</option>\
                                </select>\
                            </div>\
                        </div>';
                    html_str += '</div>';

                    $("#listLinkModal .link-section").html(html_str);

                    //hideLoading();
                });
                
            break;

            case 'exam_paper_detail': //试卷
                api = api + '?rows=5&page=' + page;
                //搜索功能
                $('#detailLinkModal .link-search input[name="api"]').val(api);
                $('#detailLinkModal .link-search').unbind("click").on('click','.btn',function(event){
                    event.preventDefault();
                    var keyword = $('#detailLinkModal .link-search input[name="keyword"]').val();
                    var api = $('#detailLinkModal .link-search input[name="api"]').val();
                    var api = api + '&keyword='+ keyword;
                    getModalData(api,'exam_paper_detail');
                });

                getModalData(api,'exam_paper_detail');
            break;

            case 'micro_page': //自定义页面
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

                    $('#detailLinkModal .link-section').html(html_str); 
                    // 获取分页器实例对象
                    var diyPager = $('#detailLinkModal .pager').data('zui.pager');
                    //动态更新分页器
                    diyPager.set(parseInt(data.data.current_page), parseInt(data.data.total), parseInt(data.data.per_page));
                    hideLoading();
                });
            break;

            case 'tominiprogram': // 跳转小程序
                //

                getModalData(api,'tominiprogram');
            break;

            default:
                //不做处理
                //hideLoading();
        }
    };

    //动态绑定分页器页码点击
    $('#detailLinkModal .pager').on('onPageChange', function(e, state, oldState) {
        if (state.page !== oldState.page) {
            generatingTable(data_obj,state.page);
        }
    });

    //选择链接内容点击事件
    $('#detailLinkModal .link-section').on('click','[data-rule="link_param"]',function(){

        var data = {};
            data.link_title = $(this).data('link-title');
            //获取link_id
            data.link_id = $(this).data('link-id');
            //获取link_type
            data.link_type = $(this).data('link-type');
            //获取链接类型的标题
            data.link_type_title = $(this).data('link-type-title');
            //获取链接所属模型
            data.link_module = $(this).data('link-module');
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
        if(this_page == 'page'){
            var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="links_list"]:eq('+link_index+')');
            //部分组件需要写入并不在该DOM中的link_title
            $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')').find('input[name="link_title"]').val(data.link_title);
        }
        //底部导航
        if(this_page == 'footer'){
            //底部导航设置页数据返回
            var this_ele = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        //PC导航页
        if(this_page == 'pc'){
            //PC导航设置页数据返回
            var this_ele = $('.pc-nav-box .pc-nav-form-item:eq('+pc_link_index+')');
        }

        this_ele.find('input[name="link_sys_type"]').val('detail');
        this_ele.find('input[name="link_title"]').val(data.link_title);
        this_ele.find('input[name="link_type"]').val(data.link_type);
        this_ele.find('input[name="link_type_title"]').val(data.link_type_title);
        this_ele.find('input[name="link_module"]').val(data.link_module);
        this_ele.find('input[name="link_url"]').val(data.link_url);
        this_ele.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        this_ele.find('.link_title li:eq(0)').html(data.link_type_title);
        this_ele.find('.link_title li:eq(1)').html(data.link_title);
        
        //关闭模态框
        $('[data-dismiss="modal"]').click();
    });

    //列表类模态框中选择项点击
    $('#listLinkModal').on('click', '.select-item', function(){
        $('#listLinkModal').find('.select-item').removeClass('select-active');
        $(this).addClass('select-active');
    });

    //列表类模态框点击确认后续数据处理
    $('#listLinkModal').on('click', '[data-rule="list-link-submit"]', function(){
        //课程类型选择
        var type = $('#listLinkModal .select-active').data('type');
        var type_title = $('#listLinkModal .select-active').data('title');
        var type_value = $('#listLinkModal .select-active').data('value');
        //获取link_cagegory_id
        var category_id = $('#listLinkModal select[name="category_id"]').val();
        var category_title = $('#listLinkModal select[name="category_id"] option:selected').text();
        var order_field = $('#listLinkModal [name="order_field"]').val();
        var order_type = $('#listLinkModal [name="order_type"]').val();
        var param = {
            category_id: category_id,
            category_title: category_title,
            type: type_value,
            type_title: type_title,
            order_field: $('#listLinkModal [name="order_field"]').val(), 
            order_type: $('#listLinkModal [name="order_type"]').val() 
        };

        //DIY页面数据返回
        if(this_page == 'page'){
            var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')');
            //部分组件需要写入并不在该DOM中的link_title
            $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')').find('input[name="link_title"]').val(data_obj.linkTypeTitle);
        }
        //底部导航
        if(this_page == 'footer'){
            //底部导航设置页数据返回
            var this_ele = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        //PC导航页
        if(this_page == 'pc'){
            //PC导航设置页数据返回
            var this_ele = $('.pc-nav-box .pc-nav-form-item:eq('+pc_link_index+')');
        }

        this_ele.find('input[name="link_sys_type"]').val('list');
        this_ele.find('input[name="link_title"]').val(category_title);
        this_ele.find('input[name="link_type"]').val(data_obj.linkType);
        this_ele.find('input[name="link_type_title"]').val(data_obj.linkTypeTitle);
        this_ele.find('input[name="link_module"]').val(data_obj.module);
        this_ele.find('[name="link_param"]').val(JSON.stringify(param));

        //按钮右侧链接文字
        this_ele.find('.link_title li:eq(0)').html(data_obj.linkTypeTitle);
        //console.log(title);
        this_ele.find('.link_title li:eq(1)').html(category_title);

        //关闭模态框
        $('[data-dismiss="modal"]').click();
    });
    

    //外部链接模态框点击确认后续数据处理
    $('#linkOutUrlModal').on('click','[data-rule="outurl-link-submit"]',function(){
        
        //获取用户输入的数据
        var data = {};
        data.link_title = $('#linkOutUrlModal').find('[data-rule="data-link-title"]').val();
        //获取link_type
        data.link_type = 'out_url';
        //获取链接类型的标题
        data.link_type_title = '自定义链接';
        //获取链接所属模型
        data.link_module = '';
        //web端链接地址
        data.link_url = $('#linkOutUrlModal').find('[data-rule="data-link-url"]').val();
        //链接参数名
        var param = {
            title: data.link_type_title,
            url:data.link_url
        };
        
        //判断下是否底部导航链接至设置
        //DIY页面数据返回
        if(this_page == 'page'){
            var this_ele = $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')');
            //部分组件需要写入并不在该DOM中的link_title
            $('[data-object-index="'+object_index+'"] [data-rule="object-controller-item"]:eq('+link_index+')').find('input[name="link_title"]').val(data_obj.linkTypeTitle);
        }
        //底部导航
        if(this_page == 'footer'){
            //底部导航设置页数据返回
            var this_ele = $('.footer-content .footer-nav-form-item:eq('+footer_link_index+')');
        }

        //PC导航页
        if(this_page == 'pc'){
            //PC导航设置页数据返回
            var this_ele = $('.pc-nav-box .pc-nav-form-item:eq('+pc_link_index+')');
        }

        this_ele.find('input[name="link_sys_type"]').val('out_url');
        this_ele.find('input[name="link_title"]').val(data.link_title);
        this_ele.find('input[name="link_type"]').val(data.link_type);
        this_ele.find('input[name="link_type_title"]').val(data.link_type_title);
        this_ele.find('input[name="link_module"]').val(data.link_module);
        this_ele.find('[name="link_param"]').val(JSON.stringify(param));
        this_ele.find('[name="link_url"]').val(data.link_url);
        
        //按钮右侧链接文字
        this_ele.find('.link_title li:eq(0)').html(data.link_type_title);
        this_ele.find('.link_title li:eq(1)').html(data.link_title);

        //简单验证表单
        var t = $('#linkOutUrlModal').find('[data-rule="data-link-title"]');
        if(data.link_title == '' || data.link_title == undefined){
            t.addClass('not-filled');
            t.focus();
            return;
        }else{
            t.removeClass('not-filled');
        }

        var u = $('#linkOutUrlModal').find('[data-rule="data-link-url"]');
        if(data.link_url == '' || data.link_url == undefined){
            
            u.addClass('not-filled');
            u.focus();
            return;
        }else{
            u.removeClass('not-filled');
        }
        
        //关闭模态框
        $('[data-dismiss="modal"]').click();
    });

    function showLoading(){
        $('.modal-body').append('<div class="big_loading"><img src="/static/common/images/big_loading.gif"/></div>');
    }

    function hideLoading(){
        $('.modal-body .big_loading').remove();
    }
});