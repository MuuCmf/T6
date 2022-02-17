/**
 * 底部导航设置页js部分
 * 
 */
 $(function(){

	//选择列数
	$('.footer-nav-box [name="colume"]').change(function(){
	    var colume = $(this).val();
		
		//遍历预览区内容的显示和隐藏
		$('.page-footer .item.custom').each(function(index){
			if(index < parseInt(colume-2)){
				$(this).addClass('enable');
				$(this).removeClass('hidden');
			}else{
				$(this).addClass('hidden');
				$(this).removeClass('enbale');
			}
		});

		//遍历控制区内容的显示和隐藏
		$('.form-horizontal.footer-nav-form-item.custom').each(function(index){
			
			if(index < parseInt(colume-2)){
				$(this).addClass('enable');
				$(this).removeClass('hidden');
			}else{
				$(this).addClass('hidden');
				$(this).removeClass('enable');
			}
		});
		//控制区新增元素
		var length = $('.form-horizontal.footer-nav-form-item.custom').length;
		//应该插入的数量
		var num = (colume-2)-length;
		//控制区新增元素
		for(var i=0;i<num;i++){
			if($('.form-horizontal.footer-nav-form-item.custom').length > 0){
				$('.form-horizontal.footer-nav-form-item.custom:last').after($('#customNavItem').html());
			}else{
				$('.form-horizontal.footer-nav-form-item.system:first').after($('#customNavItem').html());
			}
		}
		//预览区新增元素
		for(var j=0;j<num;j++){
			if($('.page-footer-preview .item.custom').length > 0){
				$('.page-footer-preview .item.custom:last').after($('#previewNavItem').html());
			}else{
				$('.page-footer-preview .item.system:first').after($('#previewNavItem').html());
			}
		}

		//遍历更改预览区内容的宽度
		$('.page-footer .item').each(function(index){
			if(colume == 2){
				$(this).css('width','50%');
			}
			if(colume == 3){
				$(this).css('width','33.33333333%');
			}
			if(colume == 4){
				$(this).css('width','25%');
			}
			if(colume == 5){
				$(this).css('width','20%');
			}
		});
	});
});

//标题框数据绑定
$(function(){

	$('.footer-nav-box').on('input propertychange','input[name="nav_title"]',function(){
		var title_index = $('.footer-nav-box .footer-nav-form-item.enable input[name="nav_title"]').index(this);
		$('.page-footer .page-footer-preview .item:eq('+title_index+') .item-text').html($(this).val());
	});
});




//底部导航数据提交
$(function(){

	//判断是否json字符串
	function isJsonString(str) {
        try {
            if (typeof JSON.parse(str) == "object") {
                return true;
            }
        } catch(e) {
        }
        return false;
    }

	var footer_nav = function(){
		var obj = {};
			obj.title = '底部导航';
	        obj.type = 'footer_nav';
	        obj.colume = $('.footer-content').find('[name="colume"]').val();
	        obj.data = new Array();

        //只遍历启用的块
        $('.form-horizontal.footer-nav-form-item.enable').each(function(){
        	var param = $(this).find('[name="link_param"]').val();
	        
	        if(isJsonString(param)){
				param = $.parseJSON(param);
			}
        	var tmp_data = {
        		nav_title : $(this).find('[name="nav_title"]').val(),
        		icon_id : $(this).find('[name="icon_id"]').val(),
            	icon_url : $(this).find('[name="icon_url"]').val(),
            	link : {
            		sys_type : $(this).find('[name="link_sys_type"]').val(),
		            type : $(this).find('[name="link_type"]').val(),
		            title : $(this).find('[name="link_title"]').val(),
		            type_title : $(this).find('[name="link_type_title"]').val(),
		            module : $(this).find('[name="link_module"]').val(),
	            	param : param,
            	}
        	};
            obj.data.push(tmp_data);
        });
        return obj;
	}

	$('[data-rule="diy_footer_submit"]').click(function(e){
		e.preventDefault();
		var data = footer_nav();
		
		//ajax提交数据
		var url = $(this).data('url');
		var query = {
			footer: data,
		};
        $.post(url,query,function(msg){
            handle_ajax(msg);
            
        },'json');
  		return false;
	})
})


