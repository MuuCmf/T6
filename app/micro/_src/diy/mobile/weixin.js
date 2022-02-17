/**
 * 自定义文本组件
 */
$(function(){

	var html_text = '';

	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;

	//点击显示控制区
	$('[data-role="page_diy_section"]').on("click",'[data-type="weixin"]',function(){
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
        
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

        //控制区确认预览按钮
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			var style = $(this).parent().parent().parent().parent().find('input[name="style"]').val();
			console.log(style);
			if(style == 0){
				
			}
			if(style == 1){
				
			}
        });
        
        //控制区内点击样式选择
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .follow-style-item',function(){
            $('[data-object-index="'+object_index+'"] .follow-style-item').removeClass('active');
            $(this).addClass('active');
            var style = $(this).data('style');
            console.log(style);
            $('[data-object-index="'+object_index+'"]').find('input[name="style"]').val(style);
        });
	});

	//加载初始数据
    let weixin_loader = function(element){
        
        let api = $('input[name="config_api"]').val();
        //默认加载接口数据
        let url = api;
        let html_text = '';
        $.get(url,function(data){
            
            if(data.code){
                if(data.data){
                    console.log(data);
                    console.log(element);
                    $(element).find('.weixin-preview .weixin-lists-box .logo').html('<img src='+data.data.cover_100+' />');
                    $(element).find('.weixin-preview .weixin-lists-box .info .title').text(data.data.follow.title);
                    $(element).find('.weixin-preview .weixin-lists-box .info .desc').text(data.data.follow.desc);
                }
            }
        });
    }
    $.muuDiy.WeixinLoader = weixin_loader;

    /**
     * 加载默认数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="weixin"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();
        //console.log(type);
        //console.log(html);
        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        $('.preview-target .object-item').each(function(index){
            var this_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',this_type+'-'+index);
            object_index = this_type+'-'+index;
        });
        //数据默认加载
        weixin_loader('[data-object-index='+object_index+']');
        
    });

});