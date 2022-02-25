$(function(){

	//初始化组件索引
	var object_index;
	//初始化组件类型
    var object_type;
	//点击显示轮播控制区
	$('.page-diy-section').on("click",'.object-item[data-type="slideshow"]',function(){
		if($(this).find('.diy-preview-controller').hasClass('show')){
			return;
		}else{
			//显示
			$('.object-item').find('.diy-preview-controller').removeClass('show');
			$(this).find('.diy-preview-controller').addClass('show');
		}
		
		object_index = $(this).data('object-index');
		object_type = $(this).data('type');
		// 上传接口
		var upload_api = $('input[name="upload_api"]').val();
		// 实例上传组件
		var uploader = WebUploader.create({
			// 选完文件后，是否自动上传。
			auto: true,
			// swf文件路径
			swf: 'Uploader.swf',
			// 文件接收服务端。
			server: upload_api,
			// 选择文件的按钮。可选。
			// 内部根据当前运行是创建，可能是input元素，也可能是flash.
			pick: {id:'[data-object-index="'+object_index+'"] .uploadImgBtn' ,multiple: false},
			// 只允许选择图片文件
			accept: {
				title: 'Image',
				extensions: 'jpg,png',
				mimeTypes: 'image/*'
			}
		});
		uploader.on('fileQueued', function (file) {
			uploader.upload();
			toast.showLoading();
		});
		/*上传成功**/
		uploader.on('uploadSuccess', function (file, data) {
			//console.log(data);
			if (data.code) {
				let _this = $('[data-object-index="'+object_index+'"]');
				var link_html = _this.find('.link-box').html();
				var html_item = '';
					html_item += '<div class="slideshow-form-item" data-rule="object-controller-item">';
					html_item += '<input type="hidden" name="img_url" value="'+data.data.attachment+'"/>';
					html_item += '<div class="form-group clearfix">';
					html_item += '	<div class="slideshow-item"><img src="'+data.data.url+'"/></div>';
					html_item += '</div>';
					html_item += '<div class="form-group link-item">'+link_html+'</div>';
					html_item += '<div class="del-item-btn"><i class="icon icon-remove-sign"></i></div>';
					html_item += '</div>';
				
				//写入控制区DOM
				_this.find('.diy-preview-controller .slideshow-img-box').append(html_item);
				
				//写入预览区域
				//移除原内部DOM
				_this.find('.swiper-wrapper .title-preview').remove();
				var slide_html = '<div class="swiper-slide">'+
									'<img src="'+data.data.url+'" class="img-responsive">'+
								'</div>';
				_this.find('.swiper-wrapper').append(slide_html);
				//重启webuploader,可多次上传
				uploader.reset();
			} else {
				toast.error(data.msg);
			}
			toast.hideLoading();
		});
		//上传完成
		uploader.on('uploadComplete', function( file ) {
			toast.hideLoading();
		});
		// 发生错误
		uploader.on( 'error', function( err ) {
			if(err = 'Q_TYPE_DENIED'){
				toast.error('不支持的文件格式');
			}
			toast.hideLoading();
		});
		/******************************************************************************/

		//控制区确认预览按钮
		$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .btn',function(){
			var style = $(this).parent().parent().find('select[name="style"]').val();
			//console.log(style);
			if(style == 0){
				$('[data-object-index="'+object_index+'"]').find('.swiper-container').removeClass('style-1');
				$('[data-object-index="'+object_index+'"]').find('.swiper-container').addClass('style-0');
			}
			if(style == 1){
				$('[data-object-index="'+object_index+'"]').find('.swiper-container').removeClass('style-0');
				$('[data-object-index="'+object_index+'"]').find('.swiper-container').addClass('style-1');
			}
		});
	});

	//判断元素是否存在
	if($('.swiper-container').length > 0){
		//轮播类库
		var mySwiper = new Swiper ('.swiper-slideshow-container', {
			loop: true,
			// 如果需要分页器
			pagination: '.swiper-pagination',
			// 如果需要前进后退按钮
			nextButton: '.swiper-button-next',
			prevButton: '.swiper-button-prev',
			// 如果需要滚动条
			//scrollbar: '.swiper-scrollbar',
		});
	}
	
	//点击删除按钮移除组件DOM
	$('.page-diy-section').on("click",'.del-item-btn',function(){
		//获取索引
		var index = $('[data-object-index="'+object_index+'"] .slideshow-form-item .del-item-btn').index(this);
		console.log(index);
		$(this).parents('.slideshow-form-item').remove();
		//移除预览区dom
		$('.swiper-wrapper').find('[data-swiper-slide-index="'+index+'"]').remove();
		//移除分页器小圆圈
		$('[data-object-index="'+object_index+'"] .swiper-pagination-bullet:eq('+index+')').remove();
	});

	//点击控制区后
	$('.page-diy-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
		e.stopPropagation();
	});

	$('.page-diy-section .object-lists').on("click",'.btn-object[data-type="slideshow"]',function(){

        let type = $(this).data('type');
        let html = $('[data-object-type="'+type+'"]').html();

        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
		$('.preview-target .object-item').each(function(index){
        	object_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',object_type+'-'+index);
            object_index = object_type +'-'+ index;
        });
    });

});

