/*图标选择模态框*/

$(function(){

	//初始化组件索引
	var object_index = '';
	//初始化图标区索引
	var icon_index = 0;

	$('.page-diy-section').on('click','[data-target="#iconModal"]',function(){
		//获取组件索引
		object_index = $(this).parents('.object-item').attr('data-object-index');
		//获取图标区索引
		icon_index = $('[data-object-index="'+object_index+'"] [data-target="#iconModal"]').index(this);
	});

	//底部导航索引
	$('.footer-content').on('click','[data-target="#iconModal"]',function(){
		//获取图标区索引
		icon_index = $('.footer-content .footer-nav-form-item.enable [data-target="#iconModal"]').index(this);
	});

	//系统图标部分
	//点击图标即选择，将图标返回给diy页面
	$('#iconModal .icon-section span').click(function(){
		var icon_id = $(this).attr('data-icon-id');
		var icon_url = $(this).attr('data-icon-url');

		$('.object-item[data-object-index="'+object_index+'"] [name="icon_id"]:eq('+icon_index+')').val(icon_id);
		$('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(icon_url);
		$('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',icon_url);

		//底部导航部分
		var icon_id = $(this).attr('data-icon-id');
		var icon_url = $(this).attr('data-icon-url');

		$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_id"]').val(icon_id);
		$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_url"]').val(icon_url);
		$('.footer-manage-section .page-footer .page-footer-preview .item:eq('+icon_index+') img').attr('src',icon_url);
		//console.log(object_index);
		//关闭模态框
		$('#iconModal .close').click();
	});

	//点击自主上传图标即选择，将图标返回给diy页面(微擎部分)
	$('#iconModal').on('click','.uploadImgBtn',function(){
		util.image('', function(a){
			console.log(object_index);
			if(object_index.indexOf("category_nav") != -1){
				$('.object-item[data-object-index="'+object_index+'"] [name="icon_id"]:eq('+icon_index+')').val(a.id);
				$('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(a.attachment);
				$('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',a.url);
			}else{
				//底部
				$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_id"]').val(a.id);;
				$('.footer-nav-box .footer-nav-form-item.enable:eq('+icon_index+')').find('[name="icon_url"]').val(a.attachment);
				$('.page-footer .page-footer-preview .item:eq('+icon_index+') img').attr('src',a.url);
			}
		});

		//关闭模态框
		$('#iconModal .close').click();
	});

	/*
	$('#iconModal').on('click','.upload-list span',function(){
	
		var icon_id = $(this).attr('data-icon-id');
		var icon_url = $(this).attr('data-icon-url');

		$('.object-item[data-object-index="'+object_index+'"] [name="icon_id"]:eq('+icon_index+')').val(icon_id);
		$('.object-item[data-object-index="'+object_index+'"] [name="icon_url"]:eq('+icon_index+')').val(icon_url);
		$('.object-item[data-object-index="'+object_index+'"] .category-nav-item:eq('+icon_index+') img').attr('src',icon_url);
		//console.log(object_index);
		//关闭模态框
		$('#iconModal .close').click();
	});
	*/
});