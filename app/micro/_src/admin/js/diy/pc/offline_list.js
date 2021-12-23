/**
 * 线下课组件
 */
 $(function(){
	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
    //列表数据接口
    var list_api = $('.btn-object[data-type="offline_list"]').data('list-api');
    //课程列表加载初始数据
    let offline_list_loader = function(rows=5, order_field= 'create_time', order_type= 'ASC', category_id= 0, element){
        var show_view = $('input#show_view').val();
        var show_sale = $('input#show_sale').val();
        var show_marking_price = $('input#show_marking_price').val();
        var show_favorites = $('input#show_favorites').val();
        //默认加载接口数据
        let url = list_api + '?category_id='+category_id+'&rows='+rows+'&order_field='+order_field+'&order_type='+order_type;
        let html_text = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        if(n.price == 0){
                            n.price = '免费';
                        }

                        html_text += '<div class="item">';
                        html_text += '<div class="image"><img src="'+ n.cover_200+'" /><div class="type-container">线下课</div></div>';
                        html_text += '<div class="content">';
                        html_text += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                        html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                        html_text += '<div class="info">';
                        html_text += '<div class="type">';
                        if(show_view == 1){
                            html_text += '<div class="view">';
                            html_text += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.handling_sales;
                            html_text += '</div>';
                        }
                        if(show_sale == 1){
                            html_text += '<div class="shopping">';
                            html_text += '   <i class="fa fa-shopping-bag" aria-hidden="true"></i> ' + n.handling_sales;
                            html_text += ' </div>';
                        }
                        if(show_favorites == 1){
                            html_text += ' <div class="favorites">';
                            html_text += '   <i class="fa fa-star-o" aria-hidden="true"></i> ';
                            html_text += '   <span class="favorites-num">' + n.handling_favorites + '</span>'; 
                            html_text += '</div>';
                        }
                        html_text += '</div>';

                        html_text += '<div class="price">￥ '+ n.price +'</div>';
                        html_text += '</div>';
                        html_text += '</div>';
                        html_text += '</div>';
                        
                    });
                }

                $(element).find('.offline-list-preview .list').html(html_text);
            }
        });
    }

	//点击显示直播课列表控制区
	$('.page-diy-pc-section').on("click",'[data-type="offline_list"]',function(e){

		//已经显示的不再触发
		if($(this).find('.diy-preview-controller').hasClass('show')){
			return;
		}else{
			$('.object-item').find('.diy-preview-controller').removeClass('show');
			$(this).find('.diy-preview-controller').addClass('show');
		}

		object_index = $(this).data('object-index');
		object_type = $(this).data('type');
		//以上部分写死就可以，先low着，以后在搞
		/******************************************************************************/

		//标题框数据处理
		$('.page-diy-pc-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			//点击确认按钮后的数据重新加载
			var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
			var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
			var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
			var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').val();
			//var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
            //样式选择小图：0 大图：1
            //var style = $('[data-object-index="'+object_index+'"] select[name="style"]').val();
			//console.log(rank);
			//执行重新加载ajax数据
			offline_list_loader(rows,order_field,order_type,category_id,'[data-object-index='+object_index+']');
		});

		//标题框数据绑定
		$('.page-diy-pc-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });
        //副标题框数据绑定
		$('.page-diy-pc-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="sub_title"]',function(){
			$('[data-object-index="'+object_index+'"] .title .sub-title').html($(this).val());
		});
        //点击控制区后
        $('.page-diy-pc-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

		//课程分类数据获取
		var category_url = $('input[name="category_api"]').val();
		//console.log(url);
		var category_html = '';
		$.get(category_url,function(data){
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
    $('.page-diy-pc-section .object-lists').on("click",'.btn-object[data-type="offline_list"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }
        let html = $('[data-object-type="'+type+'"]').html();
        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        let object_index='';

        $('.preview-target .object-item').each(function(index){
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',type+'-'+index);
            object_index = type+'-'+index;
        });
        //获取初始列表数据
        offline_list_loader(5,'create_time','ASC',0,'[data-object-index='+object_index+']');
    });

});