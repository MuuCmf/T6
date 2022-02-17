/**
 * 文章列表组件
 */
$(function(){
	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;

    var base_api = '/';
    //列表加载初始数据
    let category_tree_loader = function(app, element){

        //默认加载接口数据
        let url = base_api + app + '/' + 'admin.category/tree';
        var main_box_html = '';
        var sub_box_html = '';
        $.get(url,function(data){
            //console.log(data);
            if(data.code == 200){
                if(data.data){
                    $.each(data.data,function(i,n){
                        main_box_html += '<div class="item swiper-slide" data-id="'+n.id+'">';
                        main_box_html += n.title     
                        main_box_html += '</div>';

                        sub_box_html += '<div class="sub-items swiper-category-container" data-id="'+n.id+'"><div class="swiper-wrapper">'
                        $.each(n._child,function(l,s){
                            sub_box_html += '<div class="sub-item swiper-slide">'+s.title+'</div>'
                        })
                        sub_box_html += '</div></div>';
                    });
                }

                $(element).find('.swiper-category-container .swiper-wrapper').html(main_box_html);
                $(element).find('.sub-box').html(sub_box_html);

                new Swiper ('.swiper-category-container', {
                    loop: false,
                    initialSlide :0,
                    slidesPerView :'auto',
                });
            }
            if(data.code == 0){
                toast.error(data.msg,'danger');
            }
        });
    }
    
	//点击显示控制区
	$('.page-diy-section').on("click",'[data-type="category"]',function(){
		//已经显示的不再触发
		if($(this).find('.diy-preview-controller').hasClass('show')){
			return;
		}else{
			$('.object-item').find('.diy-preview-controller').removeClass('show');
			$(this).find('.diy-preview-controller').addClass('show');
		}

		object_index = $(this).data('object-index');
		object_type = $(this).data('type');
        //点击控制区后
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            e.stopPropagation();
        });

        //控制区确认按钮
        $('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn-submit',function(){
            //获取应用名称
            var app = $('[data-object-index="'+object_index+'"] select[name="app"]').val();
            category_tree_loader(app, '[data-object-index='+object_index+']');
            console.log(app);
            //是否显示子分类：0 不显示：1 显示
            var sub_show = $('[data-object-index="'+object_index+'"] select[name="sub_show"]').val();
            console.log(sub_show);
            
            if(sub_show == 1){
                $('[data-object-index="'+object_index+'"]').find('.sub-box').removeClass('hidden');
            }else{
                $('[data-object-index="'+object_index+'"]').find('.sub-box').addClass('hidden');
            }
        });
    });

    //轮播类库
    if($('.swiper-category-container').length > 0){
        new Swiper ('.swiper-category-container', {
            loop: false,
            initialSlide :0,
            slidesPerView :'auto',
        });
    }
    
	/**
    * rank 排列布局 0 横排 1 竖排
    **/
    /**
     * 默认加载数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="category"]',function(){

        let type = $(this).data('type');

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

        //默认第一个主分类选中

    });

});