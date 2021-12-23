$(function(){

	//初始化组件索引
	var object_index;
	//初始化组件类型
    var object_type;
	//点击显示单图控制区
	$('.page-diy-pc-section').on("click",'[data-type="single_img"]',function(){
		//console.log('显示单图控制区');
		//显示
		$('.object-item').find('.diy-preview-controller').removeClass('show');
		$(this).find('.diy-preview-controller').addClass('show');
		
		object_index = $(this).data('object-index');
		object_type = $(this).data('type');
		/******************************************************************************/
		//链接选择区
		$('[data-object-index="'+object_index+'"] .dropdown-menu a').attr('data-name',object_index);
		
	});

	// //图片上传
	// $('.page-diy-pc-section').on("click",'.single-img .uploadImgBtn',function(e){
	// 	var _this = $(this).parents('.object-item');
	// 	//console.log('uploadImagBtn');
	// 	util.image('[name="img_url"]', function(a){
	// 		//console.log(a);
	// 		_this.find('input[name="img_id"]').val(a.id);//图片ID
	// 		_this.find('input[name="img_url"]').val(a.attachment);//图片ID
	// 		_this.find('.preview').html('<img src="'+a.url+'"/>');
	// 		_this.find('.single-img-item .img').html('<img src="'+a.url+'"/>');
	// 	});
	// });

	$('.page-diy-pc-section .object-lists').on("click",'.btn-object[data-type="single_img"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();

        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        let object_index='';

        $('.preview-target .object-item').each(function(index){
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',type+'-'+index);
            object_index = type+'-'+index;
        });

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
				_this.find('input[name="img_url"]').val(data.data.attachment);//图片ID
				_this.find('.preview').html('<img src="'+data.data.url+'"/>');
				_this.find('.single-img-item .img').html('<img src="'+data.data.url+'"/>');
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