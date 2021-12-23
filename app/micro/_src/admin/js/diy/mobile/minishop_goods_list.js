$(function(){

    //初始化组件索引
    var object_index;
    //初始化组件类型
    var object_type;
    //列表数据接口
    var list_api = $('.btn-object[data-type="minishop_goods_list"]').data('list-api');
    //分类数据接口
    var category_api = $('.btn-object[data-type="minishop_goods_list"]').data('category-api');
    //列表加载初始数据
    let minishop_goods_list_loader = function(rows=2, order_field= 'create_time', order_type= 'DESC', type= 'all', category_id= 0, rank = 1, element){
        var show_view = $('input#show_view').val();
        var show_sale = $('input#show_sale').val();
        var show_favorites = $('input#show_favorites').val();

        //console.log(show_view);
        //let api = list_api;
        //默认加载接口数据
        let url = list_api + '?category_id='+category_id+'&rows='+rows+'&order_field='+order_field+'&order_type='+order_type;
        let html_text = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        
                        //竖排DOM
                        if(rank == 1){
                            html_text += '<div class="item">';
                            html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                            html_text += '<div class="content">';
                            html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
                            html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                            
                            html_text += '<div class="info">';
                            html_text += '<div class="price">￥ <span class="value">'+ n.price +'</span></div>';
                            html_text += '<div class="type text-ellipsis">';

                            if(show_view == 1){
                                html_text += '<div class="view">';
                                html_text += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.view;
                                html_text += '</div>';
                            }
                            if(show_sale == 1){
                                html_text += '<div class="shopping">';
                                html_text += '   <i class="fa fa-shopping-bag" aria-hidden="true"></i> ' + n.sales;
                                html_text += ' </div>';
                            }
                             if(show_favorites == 1){
                                html_text += ' <div class="favorites">';
                                html_text += '   <i class="fa fa-star-o" aria-hidden="true"></i> ';
                                html_text += '   <span class="favorites-num">' + n.favorites + '</span>'; 
                                html_text += '</div>';
                            }
                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                        }

                        //横排DOM
                        if(rank == 0){
                            html_text += '<div class="item">';
                            html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                            html_text += '<div class="content">';
                            html_text += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                            html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                            
                            html_text += '<div class="info">';
                            html_text += '<div class="price">￥ <span class="value">'+ n.price +'</span></div>';
                            html_text += '<div class="type">';

                            if(show_view == 1){
                                html_text += '<div class="view">';
                                html_text += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.view;
                                html_text += '</div>';
                            }
                            if(show_sale == 1){
                                html_text += '<div class="shopping">';
                                html_text += '   <i class="fa fa-shopping-bag" aria-hidden="true"></i> ' + n.sales;
                                html_text += ' </div>';
                            }
                            if(show_favorites == 1){
                                html_text += ' <div class="favorites">';
                                html_text += '   <i class="fa fa-star-o" aria-hidden="true"></i> ';
                                html_text += '   <span class="favorites-num">' + n.favorites + '</span>'; 
                                html_text += '</div>';
                            }
                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                        }
                    });
                }

                if(rank == 1){
                    $(element).find('.minishop-goods-list-preview .list').removeClass('rank0');
                    $(element).find('.minishop-goods-list-preview .list').addClass('rank1');
                }else{
                    $(element).find('.minishop-goods-list-preview .list').removeClass('rank1');
                    $(element).find('.minishop-goods-list-preview .list').addClass('rank0');
                }

                $(element).find('.minishop-goods-list-preview .list').html(html_text);
            }
        });
    }

    //点击显示云小店商品列表控制区
    $('.page-diy-section').on("click",'[data-type="minishop_goods_list"]',function(e){

        //已经显示的不再触发
        if($(this).find('.diy-preview-controller').hasClass('show')){
            return;
        }else{
            $('.object-item').find('.diy-preview-controller').removeClass('show');
            $(this).find('.diy-preview-controller').addClass('show');
        }

        object_index = $(this).data('object-index');
        object_type = $(this).data('type');
        
        /******************************************************************************/

        //数据处理
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
            //点击确认按钮后的数据重新加载
            var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
            var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
            var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
            var type = $('[data-object-index="'+object_index+'"] select[name="type"]').val();
            var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').val();
            var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
            //console.log(rank);
            //执行重新加载ajax数据
            minishop_goods_list_loader(rows,order_field,order_type,type,category_id,rank,'[data-object-index='+object_index+']');
        });

        //标题框数据绑定
        $('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
            $('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });

        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });
        //云小店分类数据获取
        var category_html = '';
        $.get(category_api, function(data){
            if(data.code){
                var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').data('category-id');
                if(category_id == 0){
                    category_html = '<option value="0" selected>所有</option>';
                }else{
                    category_html = '<option value="0">所有</option>';
                }

                $.each(data.data,function(i,n){
                    if(category_id == n.id){
                        category_html += '<option value="'+ n.id+'" selected>'+n.title+'</option>';
                    }else{
                        category_html += '<option value="'+ n.id+'">'+n.title+'</option>';
                    }
                    
                    if(n._child){
                        $.each(n._child,function(j,m){
                            if(category_id == m.id){
                                category_html += '<option value="'+ m.id+'" selected>----'+m.title+'</option>';
                            }else{
                                category_html += '<option value="'+ m.id+'">----'+m.title+'</option>';
                            }
                        })
                    }
                });
                $('[data-object-index="'+object_index+'"] select[name="category_id"]').html(category_html);
            }
        });
        
    });
	/**
     * 加载数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="minishop_goods_list"]',function(){

        let type = $(this).data('type');
        let url = $(this).data('plugin-url');

        //异步验证插件可用性
        $.get(url, function($res){
            console.log($res);
            if($res.code == 200){
                let html = $('[data-object-type="'+type+'"]').html();
                $('.preview-target').append(html);
                console.log(html);
                //为新增元素添加编号索引，避免多次引入冲突
                let object_index='';

                $('.preview-target .object-item').each(function(index){
                    var this_type = $(this).data('type');
                    //为所有已显示组件元素DOM编号索引，避免多次引入冲突
                    $(this).attr('data-object-index',this_type+'-'+index);
                    object_index = this_type+'-'+index;
                });
                //获取初始列表数据
                //if(type=='knowledge_list'){
                minishop_goods_list_loader(2,'create_time','ASC','all',0,1,'[data-object-index='+object_index+']');
                //}
            }else{
                toast.error('该组件需安装云小店模块','danger');
                return;
            }
        });
    });
})