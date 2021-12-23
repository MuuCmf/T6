$(function(){
	//点击显示图文导航控制区

	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
	//点击显示控制区
	$('.page-diy-pc-section').on("click",'[data-type="category_nav"]',function(){
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
		$('.page-diy-pc-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="nav_title"]',function(){
			
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
        
        //点击自主上传图标即选择，将图标返回给diy页面
        
       
		// util.image('', function(a){
		// //console.log(a);
		//     $('.object-item[data-object-index="'+object_index+'"] [name="icon_id"]:eq('+icon_index+')').val(a.id);
		//     $('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(a.attachment);
		//     $('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',a.url);
		// });
		
		
		//标题框数据绑定
		$('.page-diy-pc-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="title"]',function(){
			$('[data-object-index="'+object_index+'"] .title h2').html($(this).val());
		});
		//副标题框数据绑定
		$('.page-diy-pc-section').on('input propertychange','[data-object-index="'+object_index+'"] input[name="sub_title"]',function(){
			$('[data-object-index="'+object_index+'"] .title .sub-title').html($(this).val());
		});
		//标题显示隐藏下拉触发事件
		$('.page-diy-pc-section').on('change','[data-object-index="'+object_index+'"] select[name="title_show"]',function(e){
			console.log($(this).val());
			if($(this).val() == 0){
				$('[data-object-index="'+object_index+'"] .category-nav-preview .title').addClass('hidden');
			}else{
				$('[data-object-index="'+object_index+'"] .category-nav-preview .title').removeClass('hidden');
			}
		});
		

		//导航底部文字显示隐藏下拉触发事件
		$('.page-diy-pc-section').on('change','[data-object-index="'+object_index+'"] select[name="colume_title_show"]',function(e){
			console.log($(this).val());
			if($(this).val() == 0){
				$('[data-object-index="'+object_index+'"] .category-nav-preview .item-text').addClass('hidden');
			}else{
				$('[data-object-index="'+object_index+'"] .category-nav-preview .item-text').removeClass('hidden');
			}
		});

        //点击控制区后
        $('.page-diy-pc-section').on('click','[data-object-index="'+object_index+'"] .diy-preview-controller',function(e){
            //e.stopPropagation();
        });
	});

	$('.page-diy-pc-section .object-lists').on("click",'.btn-object[data-type="category_nav"]',function(){

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
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',type+'-'+index);
            object_index = type+'-'+index;
        });

		// 图片上传
		$('[data-object-index="'+object_index+'"] .uploadImgBtn').each(function(index, el){
			var icon_index = index;
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
				pick: {id: el ,multiple: false},
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
				     $('.object-item[data-object-index="'+object_index+'"] [name="icon_id"]:eq('+icon_index+')').val(data.data.id);
				     $('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(data.data.attachment);
				     $('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',data.data.url);
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
		});		
    });
});