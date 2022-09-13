/**
 * 文章列表组件
 */
 $(function(){
	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
    // 列表接口
    var list_api = $('.btn-object[data-type="articles_list"]').data('list-api');
    // 分类接口
    var category_api = $('.btn-object[data-type="articles_list"]').data('category-api');
    //列表加载初始数据
    let articles_list_loader = function(rows=2, category_id= 0, order_field= 'create_time', order_type= 'DESC', style = 0,element){

        //默认加载接口数据
        let url = list_api + '?rows='+rows+ '&category_id='+category_id+'&order_field='+order_field+'&order_type='+order_type+'&status=1';
        let html_text = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code == 200){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        //console.log(n);
                        //小图显示
                        if(style == 0){
                            html_text += '<div class="item small" data-type="article_list_item" data-id="'+ n.id +'">';
							
								html_text += '<div class="image" style="background-image:url('+n.cover_300+')"></div>';
								html_text += '<div class="content">';
									html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
									html_text += '<div class="info">';
										if(n.category){
										html_text += '<div class="category"> '+ n.category.title +'</div>';
										}
										html_text += '<div class="pop-info">';
										
                                        html_text += '<div class="view">';
                                        html_text += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.view;
                                        html_text += '</div>';
										
										html_text += '</div>';
									html_text += '</div>';
								html_text += '</div>';
			                html_text += '</div>';
                        }
                        //大图显示
                        if(style == 1){
                            html_text += '<div class="item big">';
                            html_text += '<div class="image"><img src="'+ n.cover_400+'" /></div>';
                            html_text += '<div class="content">';
                            html_text += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                            html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                            html_text += '<div class="info">';
                            html_text += '<div class="type">';

                            html_text += '<div class="view">';
                            html_text += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.view;
                            html_text += '</div>';
                            html_text += '<div class="support">';
                            html_text += '   <i class="fa fa-shopping-bag" aria-hidden="true"></i> ' + n.support;
                            html_text += ' </div>';
                        
                            html_text += ' <div class="favorites">';
                            html_text += '   <i class="fa fa-star-o" aria-hidden="true"></i> ';
                            html_text += '   <span class="favorites-num">' + n.favorites + '</span>'; 
                            html_text += '</div>';
                            
                            html_text += '</div>';

                            html_text += '</div>';
                            html_text += '</div>';
                            html_text += '</div>';
                        }
                    });

                    //写入DOM
                    $(element).find('.articles-list-preview .list').html(html_text);
                }
            }
        });
    }

	//点击显示文章列表控制区
	$('.page-diy-mobile-section').on("click",'.object-item[data-type="articles_list"]',function(){
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

		//控制区确认按钮
		$('.page-diy-mobile-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
			var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').val()
			var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
			var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
            //var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
            //样式选择小图：0 大图：1
            var style = $('[data-object-index="'+object_index+'"] select[name="style"]').val();
			//执行重新加载ajax数据
			articles_list_loader(rows, category_id, order_field, order_type, style,'[data-object-index='+object_index+']');
		});

		//标题框数据绑定
		$('.page-diy-mobile-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });
        
        //点击控制区后
        $('.page-diy-mobile-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

		//分类数据获取
		var category_html = '';
		$.get(category_api,function(data){
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
    * rank 排列布局 0 横排 1 竖排
    **/
    /**
     * 默认加载数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
     $('.page-diy-mobile-section .object-lists').on("click",'.btn-object[data-type="articles_list"]',function(){

        object_type = $(this).data('type');
		let html = $('[data-object-type="'+object_type+'"]').html();
		$('.preview-target').append(html);
		//为新增元素添加编号索引，避免多次引入冲突
		$('.preview-target .object-item').each(function(index){
			let type = $(this).data('type');
			//为所有已显示组件元素DOM编号索引，避免多次引入冲突
			$(this).attr('data-object-index',type+'-'+index);
			object_index = type+'-'+index;
		});
        //获取初始列表数据
        articles_list_loader(2,0,'create_time','ASC',0,'[data-object-index='+object_index+']');
    });

});