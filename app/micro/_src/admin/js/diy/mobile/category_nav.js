$(function(){
	//点击显示图文导航控制区

	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
	//点击显示控制区
	$('.page-diy-section').on("click",'[data-type="category_nav"]',function(){
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
		//链接选择区
		$('[data-object-index="'+object_index+'"] .dropdown-menu a').attr('data-name',object_index);

		//标题框数据处理
		$('.page-diy-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="nav_title"]',function(){
			
			var title_index = $('[data-object-index="'+object_index+'"] input[name="nav_title"]').index(this);

			$('[data-object-index="'+object_index+'"] .category-nav-item:eq('+title_index+') .item-text').html($(this).val());
			
		});

		//选择列数
		$('[data-object-index="'+object_index+'"]').on('change','[name="colume"]',function(){
			var colume = $(this).val();
			console.log(colume);
			//遍历显示区内容的显示和隐藏
			$('[data-object-index="'+object_index+'"] .category-nav-preview .category-nav-item').each(function(index){
				if(colume == 3){
					$(this).css('width','33%');
				}
				if(colume == 4){
					$(this).css('width','25%');
				}
				if(colume == 5){
					$(this).css('width','20%');
				}

				if(index < parseInt(colume)){
					$(this).addClass('enable');
					$(this).removeClass('hidden');
					//$(this).attr('data-rule','object-controller-item');
				}else{
					$(this).addClass('hidden');
					$(this).removeClass('enbale');
					//$(this).attr('data-rule','false');
				}
			});
			//遍历控制区内容的显示和隐藏
			$('[data-object-index="'+object_index+'"] .category-nav-box .category-nav-form-item').each(function(index){

				if(index < parseInt(colume)){
					$(this).addClass('enable');
					$(this).removeClass('hidden');
					$(this).attr('data-rule','object-controller-item');
				}else{
					$(this).addClass('hidden');
					$(this).removeClass('enable');
					$(this).attr('data-rule','false');
				}
			})
		});
	});

	$('.page-diy-section .object-lists').on("click",'.btn-object[data-type="category_nav"]',function(){

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
        let object_index='';

        $('.preview-target .object-item').each(function(index){
            var this_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',this_type+'-'+index);
            object_index = this_type+'-'+index;
        });
    });

});