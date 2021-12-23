$(function(){

    $('.public-header').on("click",'.config-preview',function(){
        
        //已经显示的不再触发
        if($(this).find('.config-controller').hasClass('show')){
            return;
        }else{
            $('.object-item').find('.diy-preview-controller').removeClass('show');
            $('.page-header').find('.config-controller').removeClass('show');
            $(this).find('.config-controller').addClass('show');
        }
    });

	//图片上传
	$('.page-diy-pc-section').on("click",'.public-header .uploadImgBtn',function(e){
        //console.log('通用顶部设置LOGO选择');
		var _this = $(this).siblings();
		util.image('[name="logo"]', function(a){
			//console.log(a);
			$('input[name="logo"]').val(a.attachment);//logo
            $('.public-header').find('.logo').removeClass('default-logo');
            $('.public-header').find('.logo').addClass('custom-logo');
			$('.public-header').find('.logo').html('<img src="'+a.url+'" />');
		});
	});

    //logo还原
    $('.page-diy-pc-section').on('click','.public-header .logo-default',function(e){
        var logo = $('.pc-header').data('logo');
        var title = $('.pc-header').data('title');
        var html = '<img src="'+ logo +'" /><h2>' + title + '</h2>';
        $('.public-header').find('.logo').removeClass('custom-logo');
        $('.public-header').find('.logo').addClass('default-logo');
        $('.public-header').find('.logo').html(html);

        $('input[name="logo"]').val('');//logo
    });
});