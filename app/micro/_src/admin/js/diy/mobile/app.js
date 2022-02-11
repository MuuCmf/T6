/**
 * 通用应用列表组件
 */
 $(function(){

	//初始化组件索引
	var object_index;
	//初始化组件类型
    var type;
    //列表接口
    var list_api;
    //分类数据接口
    var category_api;
    //列表加载初始数据
    let list_loader = function(params = {
        'rows': 2, 
        'order_field':  'create_time', 
        'order_type': 'DESC', 
        'type': 'all', 
        'category_id': 0, 
        'rank' : 1, 
        'style' : 0
    },element){
        var show_view = $('input#show_view').val();
        var show_sale = $('input#show_sale').val();
        var show_marking_price = $('input#show_marking_price').val();
        var show_favorites = $('input#show_favorites').val();
        // 转url参数
        var url_params = queryParams(params);
        //默认加载接口数据
        let url = list_api + url_params;
        let html_text = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        if(n.price == 0){
                            n.price = '免费';
                        }
                        //竖排DOM
                        if(params.rank == 1){
                            //小图显示
                            if(params.style == 0){
                                html_text += '<div class="item small">';
                                html_text += '<div class="image"><img src="'+ n.cover_200+'" /><div class="type-container">'+ n.type_str+'</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                                html_text += '<div class="info">';
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

                                html_text += '<div class="price">￥ '+ n.price +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                            //大图显示
                            if(params.style == 1){
                                html_text += '<div class="item big">';
                                html_text += '<div class="image"><img src="'+ n.cover_200+'" /><div class="type-container">'+ n.type_str+'</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                                html_text += '<div class="info">';
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

                                html_text += '<div class="price">￥ '+ n.price +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                        }
                        //横排DOM
                        if(params.rank == 0){
                            html_text += '<div class="item">';
                            html_text += '<div class="image"><img src="'+ n.cover_200+'" /><div class="type-container">'+ n.type_str+'</div></div>';
                            html_text += '<div class="content">';
                            html_text += '<div class="title h3 text-ellipsis-2">'+ n.title +'</div>';
                            html_text += '<div class="description text-ellipsis">'+ n.description +'</div>';
                            html_text += '<div class="info">';
                            html_text += '<div class="price">￥ '+ n.price +'</div>';
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

                if(params.rank == 1){
                    $(element).find('.app-list-preview .list').removeClass('rank0');
                    $(element).find('.app-list-preview .list').addClass('rank1');
                }else{
                    $(element).find('.app-list-preview .list').removeClass('rank1');
                    $(element).find('.app-list-preview .list').addClass('rank0');
                }

                $(element).find('.app-list-preview .list').html(html_text);
            }
        });
    }

	//点击显示列表控制区
	$('.page-diy-section').on("click",'[data-type="app_list"]',function(e){
        
        $('.object-item').find('.diy-preview-controller').removeClass('show');
        $(this).find('.diy-preview-controller').addClass('show');
		/******************************************************************************/

		//点击确认按钮后的数据重新加载
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(e){
            e.stopPropagation();
			//点击确认按钮后的数据重新加载
			var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
			var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
			var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
			var type = $('[data-object-index="'+object_index+'"] select[name="type"]').val();
			var category_id = $('[data-object-index="'+object_index+'"] select[name="category_id"]').val();
            var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
            //样式选择小图：0 大图：1
            var style = $('[data-object-index="'+object_index+'"] select[name="style"]').val();
			//console.log(rank);
			//执行重新加载ajax数据
			list_loader({
                'rows': rows, 
                'order_field':  order_field, 
                'order_type': order_type, 
                'category_id': category_id, 
                'rank' : rank, 
                'style' : style
            },'[data-object-index='+object_index+']');
        });
        
		//标题框数据绑定
		$('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });
        
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

		//课程分类数据获取
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
    $('.page-diy-section .object-lists').on("click",'.btn-object',function(){

        type = $(this).data('type');
        list_api = $(this).data('list-api');
        console.log(list_api);
        //分类数据接口
        category_api = $(this).data('category-api');

        var html = $('[data-object-type="app_list"]').html();
        $('.preview-target').append(html);

        $('.preview-target .object-item').each(function(index){
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',type+'-'+index);
            object_index = type+'-'+index;
        });
        // 初始加载列表数据
        list_loader({
            'rows': 2, 
            'order_field':  'create_time', 
            'order_type': 'DESC', 
            'category_id': 0, 
            'rank' : 1, 
            'style' : 0
        },'[data-object-index='+object_index+']');
        
    });

    /**
     * 对象转url参数
     * @param {*} data,对象
     * @param {*} isPrefix,是否自动加上"?"
     */
    function queryParams(data = {}, isPrefix = true, arrayFormat = 'brackets') {
        let prefix = isPrefix ? '?' : ''
        let _result = []
        if (['indices', 'brackets', 'repeat', 'comma'].indexOf(arrayFormat) == -1) arrayFormat = 'brackets';
        for (let key in data) {
            let value = data[key]
            // 去掉为空的参数
            if (['', undefined, null].indexOf(value) >= 0) {
                continue;
            }
            // 如果值为数组，另行处理
            if (value.constructor === Array) {
                // e.g. {ids: [1, 2, 3]}
                switch (arrayFormat) {
                    case 'indices':
                        // 结果: ids[0]=1&ids[1]=2&ids[2]=3
                        for (let i = 0; i < value.length; i++) {
                            _result.push(key + '[' + i + ']=' + value[i])
                        }
                        break;
                    case 'brackets':
                        // 结果: ids[]=1&ids[]=2&ids[]=3
                        value.forEach(_value => {
                            _result.push(key + '[]=' + _value)
                        })
                        break;
                    case 'repeat':
                        // 结果: ids=1&ids=2&ids=3
                        value.forEach(_value => {
                            _result.push(key + '=' + _value)
                        })
                        break;
                    case 'comma':
                        // 结果: ids=1,2,3
                        let commaStr = "";
                        value.forEach(_value => {
                            commaStr += (commaStr ? "," : "") + _value;
                        })
                        _result.push(key + '=' + commaStr)
                        break;
                    default:
                        value.forEach(_value => {
                            _result.push(key + '[]=' + _value)
                        })
                }
            } else {
                _result.push(key + '=' + value)
            }
        }
        return _result.length ? prefix + _result.join('&') : ''
    }

});