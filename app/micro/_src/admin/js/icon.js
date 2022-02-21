/*图标选择模态框*/

$(function(){

	//初始化组件索引
	var object_index = '';
	//初始化图标区索引
	var icon_index = 0;

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
		pick: {id:'#iconModal .uploadImgBtn' ,multiple: false},
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
			$('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(data.data.url);
			$('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',data.data.url);

			$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_url"]').val(data.data.url);
			$('.footer-manage-section .page-footer .page-footer-preview .item:eq('+icon_index+') img').attr('src',data.data.url);
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

	// 页面DIY组件索引获取
	$('.page-diy-section').on('click','[data-target="#iconModal"]',function(){
		//获取组件索引
		object_index = $(this).parents('.object-item').attr('data-object-index');
		//获取图标区索引
		icon_index = $('[data-object-index="'+object_index+'"] [data-target="#iconModal"]').index(this);
	});

	//底部导航索引获取
	$('.footer-content').on('click','[data-target="#iconModal"]',function(){
		//获取图标区索引
		icon_index = $('.footer-content .footer-nav-form-item.enable [data-target="#iconModal"]').index(this);
	});

	//系统图标部分
	//点击图标即选择，将图标返回给diy页面
	$('#iconModal .icon-section span').click(function(){
		var icon_url = $(this).attr('data-icon-url');
		$('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(icon_url);
		$('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',icon_url);

		//底部导航部分
		var icon_url = $(this).attr('data-icon-url');
		$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_url"]').val(icon_url);
		$('.footer-manage-section .page-footer .page-footer-preview .item:eq('+icon_index+') img').attr('src',icon_url);
		//console.log(object_index);
		//关闭模态框
		$('#iconModal .close').click();
	});

	//点击自主上传图标即选择，将图标返回给diy页面
	$('#iconModal').on('click','.uploadImgBtn',function(){

		//关闭模态框
		$('#iconModal .close').click();
	});
});