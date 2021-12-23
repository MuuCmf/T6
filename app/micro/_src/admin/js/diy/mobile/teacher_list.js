/**
 * 自定义文本组件
 */
$(function(){

	var html_text = '';

	//初始化组件索引
	var object_index;
	//初始化组件类型
    var object_type;
    var list_api = $('.btn-object[data-type="teacher_list"]').data('list-api');
    //讲师列表加载初始数据
    let teacher_list_loader = function(rows=2, order_field= 'create_time', order_type= 'DESC', rank = 1, element){
        //默认加载接口数据
        let url = list_api + '?rows='+rows+'&order_field='+order_field+'&order_type='+order_type+'&status=1';
        let html_text = '';
        $.get(url,function(data){
            
            if(data.code){
                if(data.data){
                    
                    $.each(data.data.data,function(i,n){
                        
                        html_text += '<div class="item">';
                        html_text += '<div class="image"><img src="'+ n.cover_200+'" /></div>';
                        html_text += '<div class="content">';
                        html_text += '<div class="title h3 text-ellipsis">'+ n.name +'</div>';
                        html_text += '<div class="description text-ellipsis-2">'+ n.description +'</div>';
                        html_text += '<div class="info">';

                        html_text += '</div>';
                        html_text += '</div>';
                        html_text += '</div>';
                    });

                    if(rank == 1){
                        $(element).find('.teacher-list-preview .list').removeClass('rank0');
                        $(element).find('.teacher-list-preview .list').addClass('rank1');
                    }else{
                        $(element).find('.teacher-list-preview .list').removeClass('rank1');
                        $(element).find('.teacher-list-preview .list').addClass('rank0');
                    }
                    
                    $(element).find('.teacher-list-preview .list').html(html_text);
                }
            }
        });
    }

	//点击显示专栏列表控制区
	$('.page-diy-section').on("click",'[data-type="teacher_list"]',function(){
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

		//数据处理
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			
			var rows = $('[data-object-index="'+object_index+'"] input[name="rows"]').val();
			var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
            var order_type = $('[data-object-index="'+object_index+'"] select[name="order_type"]').val();
			var rank = $('[data-object-index="'+object_index+'"] select[name="rank"]').val();
			//执行重新加载ajax数据
			teacher_list_loader(rows,order_field,order_type,rank,'[data-object-index='+object_index+']');
			
		});

		//标题框数据绑定
		$('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h3').html($(this).val());
        });
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });
	});

	

    /**
     * 加载默认数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="teacher_list"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        //console.log(open);
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();
        console.log(type);
        //console.log(html);
        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        $('.preview-target .object-item').each(function(index){
            var this_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',this_type+'-'+index);
            object_index = this_type+'-'+index;
        });
        //讲师数据默认加载
        if(type=='teacher_list'){
            teacher_list_loader(2,'create_time','DESC',1,'[data-object-index='+object_index+']');
        }
    });

});