/**
 * 自定义文本组件
 */
$(function(){

	//var api = '/knowledge/api/knowledgeList';

	//默认加载接口数据
	//var url = api + '?type=list&rows=3';
	var html_text = '';

	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
    //列表数据接口
    var list_api = $('.btn-object[data-type="miniprogram_live_list"]').data('list-api');
    //课程列表加载初始数据
    let miniprogram_live_list_loader = function(rows=2, rank = 1, style = 0, element){

        //默认加载接口数据
        let url = list_api;
        let html_text = '';
        let data = {
            'rows' : rows,
        };
        $.post(url,data,function(res){
            console.log(res);
            if(res.code){
                if(res.data){
                    $.each(res.data.data,function(i,n){
                        //竖排DOM
                        if(rank == 1){
                            //小图显示
                            if(style == 0){
                                html_text += '<div class="item small" data-id="'+ n.roomid +'">';
                                html_text += '<div class="image"><img src="'+ n.share_img+'" /><div class="type-container">小直播</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2"><span class="label">'+ n.live_status_str+'</span> '+ n.name +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.start_time_str +' - '+ n.end_time_str +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                            //大图显示
                            if(style == 1){
                                html_text += '<div class="item big" data-id="'+ n.roomid +'">';
                                html_text += '<div class="image"><img src="'+ n.share_img+'" /><div class="type-container">小直播</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2"><span class="label">'+ n.live_status_str+'</span> '+ n.name +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.start_time_str +' - '+ n.end_time_str +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                        }
                        //横排DOM
                        if(rank == 0){
                            //小图显示
                            if(style == 0){
                                html_text += '<div class="item" data-id="'+ n.roomid +'">';
                                html_text += '<div class="image"><img src="'+ n.share_img+'" /><div class="type-container">小直播</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2"><span class="label">'+ n.live_status_str+'</span> '+ n.name +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.start_time_str +' - '+ n.end_time_str +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                            //大图显示
                            if(style == 1){
                                html_text += '<div class="item big" data-id="'+ n.roomid +'">';
                                html_text += '<div class="image"><img src="'+ n.share_img+'" /><div class="type-container">小直播</div></div>';
                                html_text += '<div class="content">';
                                html_text += '<div class="title h3 text-ellipsis-2"><span class="label">'+ n.live_status_str+'</span> '+ n.name +'</div>';
                                html_text += '<div class="description text-ellipsis">'+ n.start_time_str +' - '+ n.end_time_str +'</div>';
                                html_text += '</div>';
                                html_text += '</div>';
                            }
                        }
                        
                    });
                }

                if(rank == 1){
                    $(element).find('.live-list-preview .list').removeClass('rank0');
                    $(element).find('.live-list-preview .list').addClass('rank1');
                }else{
                    $(element).find('.live-list-preview .list').removeClass('rank1');
                    $(element).find('.live-list-preview .list').addClass('rank0');
                }

                $(element).find('.live-list-preview .list').html(html_text);
            }
        });
    }

	//点击显示直播列表控制区
	$('.page-diy-section').on("click",'[data-type="miniprogram_live_list"]',function(e){

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
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			//点击确认按钮后的数据重新加载
			var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
			var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
            //样式选择小图：0 大图：1
            var style = $('[data-object-index="'+object_index+'"] select[name="style"]').val();
			//console.log(rank);
			//执行重新加载ajax数据
			miniprogram_live_list_loader(rows,rank,style,'[data-object-index='+object_index+']');
		});

		//标题框数据绑定
		$('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });
        
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

        //排列布局选项选择触发事件
		$('.page-diy-section').on('change','[data-object-index="'+object_index+'"] [name="rank"]',function(){
            //alert('排列布局选择');
            var rank = $(this).val();
            if(rank == 1){
                //alert('选择了竖排');
            }
		});
	});

    /**
     * 加载默认数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="miniprogram_live_list"]',function(){

        var _this = $(this);
        let type = _this.data('type');
        let open = _this.data('open');
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }
        let plugin_name = $(this).data('plugin_name');
        let url = $(this).data('plugin-url');
        
        //异步验证插件可用性
        $.get(url, function($res){
            //console.log($res);
            if($res.code == 200){
                var html = $('[data-object-type="'+type+'"]').html();
                $('.preview-target').append(html);
                //为新增元素添加编号索引，避免多次引入冲突
                var object_index='';

                $('.preview-target .object-item').each(function(index){
                    var this_type = $(this).data('type');
                    //为所有已显示组件元素DOM编号索引，避免多次引入冲突
                    $(this).attr('data-object-index',this_type+'-'+index);
                    object_index = this_type+'-'+index;
                });
            }else{
                toast.error('该组件需安装直播插件','danger');
                return false;
            }

            //获取初始列表数据
            miniprogram_live_list_loader(2,1,0,'[data-object-index='+object_index+']');
        });
    });

});