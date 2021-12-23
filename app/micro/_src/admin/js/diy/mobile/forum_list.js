/**
 * 自定义文本组件
 */
 $(function(){

    //初始化组件索引
    var object_index;
    //初始化组件类型
    var object_type;
    var list_api = $('.btn-object[data-type="forum_list"]').data('list-api');
    //分类数据接口
    var category_api = $('.btn-object[data-type="forum_list"]').data('category-api');
    //社群列表加载初始数据
    let forum_list_loader = function(rows=2, order_field= 'create_time', order_type= 'DESC', expense= 'all', category_id= 0, rank = 1, style = 0,element){
        var show_view = $('input#show_view').val();
        var show_reply = $('input#show_reply').val();
        var show_marking_price = $('input#show_marking_price').val();
        var show_praise = $('input#show_praise').val();
        //默认加载接口数据
        let url = list_api + '?expense='+expense+'&category_id='+category_id+'&rows='+rows+'&order_field='+order_field+'&order_type='+order_type;
        let html_text = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        //是否显示划线价格
                        if (show_marking_price == 0){
                            n.marking_price = '';
                        }
                        //判断当前社群 是否收费
                        if(n.expense == 1){
                            n.marking_price = '';
                            n.price = '￥ 免费';
                        }else {
                            n.price = '￥ ' + n.price;
                        }
                        //竖排DOM
                        if(rank == 1){
                            //小图显示 
                            if(style == 0){
                                html_text += '<div class="item small">';
                                html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
                                html_text += '<div class="mbmber"> ';
                                    html_text += '<span class="mbmber-item">'+n.member_total+'成员</span>';
                                    html_text += '<span>'+n.post_total+'动态</span>';
                                html_text += '</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                            //大图显示
                            if(style == 1){
                                html_text += '<div class="item big">';
                                html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                                html_text += '<div class="mbmber"> ';
                                    html_text += '<span class="mbmber-item">'+n.member_total+'成员</span>';
                                    html_text += '<span>'+n.post_total+'动态</span>';
                                html_text += '</div>';
                                html_text += '<div class="photo"><img src="'+n.manager_user_info['avatar']+'"> </div>'

                                html_text += '</div>';
                                html_text += '</div>';
                            }
                        }
                        //横排DOM
                        if(rank == 0){
                            html_text += '<div class="item">';
                            html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                            html_text += '<div class="content">';
                            html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
                            html_text += '<div class="mbmber"> ';
                                html_text += '<span class="mbmber-item">'+n.member_total+'成员</span>';
                                html_text += '<span>'+n.post_total+'动态</span>';
                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                        }
                    });
                }

                if(rank == 1){
                    $(element).find('.forum-list-preview .list').removeClass('rank0');
                    $(element).find('.forum-list-preview .list').addClass('rank1');
                }else{
                    $(element).find('.forum-list-preview .list').removeClass('rank1');
                    $(element).find('.forum-list-preview .list').addClass('rank0');
                }

                $(element).find('.forum-list-preview .list').html(html_text);
            }
        });
    }

    //点击显示列表控制区
    $('.page-diy-section').on("click",'[data-type="forum_list"]',function(e){

        $('.object-item').find('.diy-preview-controller').removeClass('show');
        $(this).find('.diy-preview-controller').addClass('show');
        object_index = $(this).data('object-index');
        object_type = $(this).data('type');

        //判断当前rank状态
        var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
        
        if(rank == 0){
            //移除大图选项
            $('[data-object-index="'+object_index+'"] select[name="style"] option:last').remove()
        }
        $('[data-object-index="'+object_index+'"] select[name="rank"]').on('change',function(){
            rank = $(this).val();
            if(rank == 1){
                $('[data-object-index="'+object_index+'"] select[name="style"]').append("<option value='1'>大图</option>")
            }else{
                $('[data-object-index="'+object_index+'"] select[name="style"] option:last').remove()
            }
        })
        /******************************************************************************/

        //点击确认按钮后的数据重新加载
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(e){
            e.stopPropagation();
            //点击确认按钮后的数据重新加载
            var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
            var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
            var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
            var expense = $('[data-object-index="'+object_index+'"] select[name="expense"]').val();
            var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').val();
            
            //样式选择小图：0 大图：1
            var style = $('[data-object-index="'+object_index+'"] select[name="style"]').val();
            //console.log(rank);
            //执行重新加载ajax数据
            forum_list_loader(rows,order_field,order_type,expense,category_id,rank,style,'[data-object-index='+object_index+']');
        });

        //标题框数据绑定
        $('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
            $('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });

        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

        //社群分类数据获取
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
     * 加载默认数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="forum_list"]',function(){

        let type = $(this).data('type');
        let url = $(this).data('plugin-url');

        //异步验证插件可用性
        $.get(url, function($res){
            console.log($res);
            if($res.code == 200){
                let html = $('[data-object-type="'+type+'"]').html();
                $('.preview-target').append(html);
                //为新增元素添加编号索引，避免多次引入冲突
                let object_index='';

                $('.preview-target .object-item').each(function(index){
                    var this_type = $(this).data('type');
                    //为所有已显示组件元素DOM编号索引，避免多次引入冲突
                    $(this).attr('data-object-index',this_type+'-'+index);
                    object_index = this_type+'-'+index;
                });
                //获取初始列表数据
                if(type=='forum_list'){
                    forum_list_loader(2,'create_time','ASC','all',0,1,0,'[data-object-index='+object_index+']');
                }
            }else{
                toast.error('该组件需安装付费社群模块','danger');
                return;
            }
        });
    });

});