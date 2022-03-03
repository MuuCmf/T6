$(function(){
	//数据提交
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

	//搜索块
	var search = function(element){
		var obj = {};
		obj.title = '搜索';
	    obj.type = 'search';
		obj.app = 'micro';
	    return obj;
	}

	//公告块
	var announce = function(element){
		var obj = {};
		obj.title = '公告';
	    obj.type = 'announce';
		obj.app = 'micro';
	    obj.data = {
	    	rows : $(element).find('[name="rows"]').val(),
	    };
	    return obj;
	}
	//单图广告
	var single_img = function(element){
		
		var param = $(element).find('[name="link_param"]').val();
		if(isJsonString(param)){
			param = $.parseJSON(param);
		}

		var obj = {};
			obj.title = '单图广告';
			obj.type = 'single_img';
			obj.app = 'micro';
			obj.style = $(element).find('[name="style"]').val(); //样式
	        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
			obj.data = new Array();
	        // obj.data = {
	        // 	img_url : $(element).find('[name="img_url"]').val(),
	        // 	link : {
	        // 		sys_type : $(element).find('[name="link_sys_type"]').val(),
		    //         type : $(element).find('[name="link_type"]').val(),
		    //         title : $(element).find('[name="link_title"]').val(),
		    //         type_title : $(element).find('[name="link_type_title"]').val(),
		    //         module : $(element).find('[name="link_module"]').val(),
	        //     	param : param,
	        // 	}
	        // };

			$(element).find('[data-rule="object-controller-item"]').each(function(){
				var param = $(this).find('[name="link_param"]').val();
					if(isJsonString(param)){
						param = $.parseJSON(param);
					}
				//console.log(param);
				var tmp_data = {
					img_url : $(this).find('[name="img_url"]').val(),
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
	//轮播图
	var slideshow = function(element){
		
		var obj = {};
		obj.title = '轮播';
		obj.type = 'slideshow';
		obj.app = 'micro';
		obj.style = $(element).find('[name="style"]').val(), //样式
        obj.data = new Array();
        //
        $(element).find('[data-rule="object-controller-item"]').each(function(){
        	var param = $(this).find('[name="link_param"]').val();
        		if(isJsonString(param)){
					param = $.parseJSON(param);
				}
			//console.log(param);
        	var tmp_data = {
            	img_url : $(this).find('[name="img_url"]').val(),
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

	//分类&筛选组件
	var category = function(element){
		var obj = {};
		obj.title = '分类';
		obj.type = 'category';
		obj.app = 'micro';
		obj.data = {
			'app' : $(element).find('select[name="app"]').val(),
			'sub_show' : $(element).find('select[name="sub_show"]').val(),
		}
		return obj;
	}

	//图文导航
	var category_nav = function(element){
		var obj = {};
		obj.title = '图文导航';
        obj.type = 'category_nav';
		obj.app = 'micro';
		obj.data_title = $(element).find('[name="title"]').val(), //数据标题
		obj.data_sub_title = $(element).find('[name="sub_title"]').val(), //数据标题
		obj.data_title_show = $(element).find('[name="title_show"]').val(), //是否显示数据标题
		obj.colume = $(element).find('[name="colume"]').val();
		obj.colume_title_show = $(element).find('[name="colume_title_show"]').val(); //是否显示导航底部文字标题
        obj.data = new Array();
        //只遍历启用的块
        $(element).find('[data-rule="object-controller-item"]').each(function(){
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
        	//console.log(index);
            obj.data.push(tmp_data);
        });
        return obj;
	}

	//自定义文本组件
	var custom_text = function(element){
		var obj = {};
		obj.title = '自定义文本';
		obj.type = 'custom_text';
		obj.app = 'micro';
		obj.data = {
			'content' : $(element).find('textarea[name="content"]').val(),
		}
		return obj;
	}

	//自定义Html组件
	var custom_html = function(element){
		var obj = {};
		obj.title = '自定义Html';
		obj.type = 'custom_html';
		obj.app = 'micro';
		obj.data = {
			'content' : $(element).find('textarea[name="content"]').val(),
		}
		return obj;
	}

	//关注公众号
	var weixin = function(element){
		var obj = {};
		obj.title = '关注公众号';
		obj.type = 'weixin';
		obj.app = 'micro';
		obj.style = $(element).find('[name="style"]').val(); //样式
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        return obj;
	}

	//会员块
	var member = function(element){
		var obj = {};
		obj.title = '会员';
	    obj.type = 'member';
		obj.app = 'micro';
	    return obj;
	}

	// //通用应用列表组件
	// var app_list = function(element){
	// 	var obj = {};
	// 	obj.title = '课程列表';
    //     obj.type = 'knowledge_list';
	// 	obj.app = 'classroom';
    //     //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
    //     obj.data = {
	// 		'title': $(element).find('[name="title"]').val(),
	// 		'sub_title': $(element).find('[name="sub_title"]').val(),
    //     	'rows': $(element).find('[name="rows"]').val(),
    //     	'type': $(element).find('[name="type"]').val(),
    //     	'category_id': $(element).find('[name="category_id"]').val(),
    //     	'order_field': $(element).find('[name="order_field"]').val(),
    //     	'order_type': $(element).find('[name="order_type"]').val(),
	// 		'rank': $(element).find('[name="rank"]').val(),
	// 		'style': $(element).find('[name="style"]').val(),
    //     };
    //     return obj;
	// }

	//点击提交按钮处理提交数据
	
	$('[type="button"][data-rule="diy_page_submit"]').click(function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var type = $(this).data('type');

		var data = new Array();
		$('.preview-target .object-item').each(function(index,element){
			//console.log(element);
			var obj = {};
			var otype = $(this).data('type');
			
			//根据组件类型获取数据
			switch (otype) {
				case ('search'):
					obj = search(element);
				break;
				case ('announce'):
					obj = announce(element);
				break;
	            case ('single_img')://单图广告
	                obj = single_img(element);
	            break;
	            case ('slideshow'):
	            	obj = slideshow(element);
	            break;
				case ('category'):
	            	obj = category(element);
	            break;
	            case ('category_nav'):
	            	obj = category_nav(element);
	            break;
	            case ('custom_text'):
	            	obj = custom_text(element);
	            break;
				case ('custom_html'):
	            	obj = custom_html(element);
	            break;
	            case ('weixin'):
	            	obj = weixin(element);
	            break;
	            case ('member'):
	            	obj = member(element);
	            break;

				default: 
					obj = $(this).find('form[data-type="'+otype+'"]').serializeArray();
					var formData = {};
					$.each(obj, function() {
						formData[this.name] = this.value;
					});
					obj = formData;
	        }
	        data.push(obj);
		});

		var header = {
			'style': $('.public-header .config-controller select[name="style"]').val(),
			'logo' : $('.public-header .config-controller input[name="logo"]').val(),
		};

		//ajax提交数据
		var url = $('[name="url"]').val();
		var query = {
			id: id,
			type: type,
			title: $('.config-controller [name="title"]').val(),//页面标题
			description: $('.config-controller [name="description"]').val(),//页面简短描述
			header: header, //通用头部配置数据
			footer_show: $('.config-controller [name="footer_show"]').val(),//仅支持移动端
			data: data,
		};
		//console.log(query);return false;
        $.post(url,query,function(msg){
            handle_ajax(msg);
        },'json');
  		return false;
	});

});