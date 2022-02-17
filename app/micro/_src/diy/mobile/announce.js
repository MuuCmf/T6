/**
 * 公告块组件
 */
$(function(){
	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;

	//点击显示单图控制区
	$('.page-diy-section').on("click",'[data-type="announce"]',function(){
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
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			//点击确认按钮后的数据重新加载
			var rows = $('[data-object-index="'+object_index+'"] select[name="rows"]').val();
			var order_field = $('[data-object-index="'+object_index+'"] select[name="order_field"]').val();
			//console.log(rows);
			//执行重新加载ajax数据
			$.muuDiy.AnnounceListLoader(rows,'[data-object-index='+object_index+']');
        });
        
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

	});

    //轮播类库
    if($('.swiper-announce-container').length > 0){
        var mySwiper = new Swiper ('.swiper-announce-container', {
            loop: true,
            direction : 'vertical',
            autoplay : 3000,
            speed:300,
        });
    }
   

	let announce_list_loader = function(rows = 2,element){

        let api = $('input[name="announce_api"]').val();
        //默认加载接口数据
        let url = api + '?rows='+rows;
        let html_text = '';
        $.get(url,function(data){
            console.log(data);
            if(data.code == 200){
                if(data.data){
                    $.each(data.data.data,function(i,n){
                        html_text += '<li class="item" data-id=" ' +n.id+ ' ">';
                        html_text += '' +n.title+ '';
                        html_text += '</li>';
                    })

                    //写入DOM
                    $(element).find('.announce-lists-box ul').html(html_text);
                }
            }
        });
    }
    $.muuDiy.AnnounceListLoader = announce_list_loader;

    /**
     * 加载数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="announce"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        //console.log(open);
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();
        console.log(type);
        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        let object_index='';

        $('.preview-target .object-item').each(function(index){
            var this_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',this_type+'-'+index);
            object_index = this_type+'-'+index;
        });
        //公告接口默认数据加载
        if(type == 'announce'){
            $.muuDiy.AnnounceListLoader(2,'[data-object-index='+object_index+']');
        }
    });

});