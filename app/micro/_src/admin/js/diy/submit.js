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
	    return obj;
	}

	//公告块
	var announce = function(element){
		var obj = {};
		obj.title = '公告';
	    obj.type = 'announce';

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
			obj.style = $(element).find('[name="style"]').val(), //样式
	        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
	        obj.data = [{
	        	img_id : $(element).find('[name="img_id"]').val(),
	        	img_url : $(element).find('[name="img_url"]').val(),
	        	link : {
	        		sys_type : $(element).find('[name="link_sys_type"]').val(),
		            type : $(element).find('[name="link_type"]').val(),
		            title : $(element).find('[name="link_title"]').val(),
		            type_title : $(element).find('[name="link_type_title"]').val(),
		            module : $(element).find('[name="link_module"]').val(),
	            	param : param,
	        	}
	        }];

        return obj;
	}
	//轮播图
	var slideshow = function(element){
		
		var obj = {};
		obj.title = '轮播';
		obj.type = 'slideshow';
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
        		img_id : $(this).find('[name="img_id"]').val(),
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

	//图文导航
	var category_nav = function(element){
		var obj = {};
		obj.title = '图文导航';
        obj.type = 'category_nav';
		
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
		obj.data = {
			'content' : $(element).find('textarea[name="content"]').val(),
		}
		return obj;
	}

	//专栏列表组件
	var column_list = function(element){
		var obj = {};
		obj.title = '专栏列表';
        obj.type = 'column_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
			'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//课程列表组件
	var knowledge_list = function(element){
		var obj = {};
		obj.title = '课程列表';
        obj.type = 'knowledge_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'type': $(element).find('[name="type"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
			'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//讲师列表组件
	var teacher_list = function(element){
		var obj = {};
		obj.title = '讲师列表';
        obj.type = 'teacher_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        };
        return obj;
	}
	//关注公众号
	var weixin = function(element){
		var obj = {};
		obj.title = '关注公众号';
		obj.type = 'weixin';
		obj.style = $(element).find('[name="style"]').val(); //样式
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        return obj;
	}

	//会员块
	var member = function(element){
		var obj = {};
		obj.title = '会员';
	    obj.type = 'member';
	    return obj;
	}

	//积分商品
	var scoreshop_goods_list = function(element){
		var obj = {};
		obj.title = '积分商品';
        obj.type = 'scoreshop_goods_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'type': $(element).find('[name="type"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        };
        return obj;
	}

	//云小店商品
	var minishop_goods_list = function(element){
		var obj = {};
		obj.title = '云小店商品';
        obj.type = 'minishop_goods_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        };
        return obj;
	}

	//线下活动
	var activity_list = function(element){
		var obj = {};
		obj.title = '线下活动';
		obj.type = 'activity_list';
		//为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
		obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
			'rows': $(element).find('[name="rows"]').val(),
			'category_id': $(element).find('[name="category_id"]').val(),
			'order_field': $(element).find('[name="order_field"]').val(),
			'order_type': $(element).find('[name="order_type"]').val(),
			'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
		};
		return obj;
	}

	//直播课列表组件
	var live_list = function(element){
		var obj = {};
		obj.title = '直播课列表';
        obj.type = 'live_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
        	'title': $(element).find('[name="title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        	'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//小程序直播组件列表组件
	var miniprogram_live_list = function(element){
		var obj = {};
		obj.title = '小程序直播组件';
        obj.type = 'miniprogram_live_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
        	'title': $(element).find('[name="title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        	'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//线下课列表组件
	var offline_list = function(element){
		var obj = {};
		obj.title = '线下课列表';
        obj.type = 'offline_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
        	'title': $(element).find('[name="title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
        	'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//资料下载列表组件
	var material_list = function(element){
		var obj = {};
		obj.title = '资料下载列表';
        obj.type = 'material_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
        	'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
			'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//试卷列表组件
	var exam_paper_list = function(element){
		var obj = {};
		obj.title = '试卷列表';
		obj.type = 'exam_paper_list';
		//为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
		obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
			'rows': $(element).find('[name="rows"]').val(),
			'category_id': $(element).find('[name="category_id"]').val(),
			'order_field': $(element).find('[name="order_field"]').val(),
			'order_type': $(element).find('[name="order_type"]').val(),
			'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
		};
		return obj;
	}

	//文章列表组件
	var article_list = function(element){
		var obj = {};
		obj.title = '文章列表';
        obj.type = 'article_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
        	'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'category_id': $(element).find('[name="category_id"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//分类&筛选组件
	var category = function(element){
		var obj = {};
		obj.title = '分类';
		obj.type = 'category';
		obj.data = {
			'sub_show' : $(element).find('select[name="sub_show"]').val(),
		}
		return obj;
	}

	//社群列表
	var forum_list = function(element){
		var obj = {};
		obj.title = '社群列表';
        obj.type = 'forum_list';
        //为了兼任性，经单图或其它单独调用链接至组件的组件写入数组内
        obj.data = {
			'title': $(element).find('[name="title"]').val(),
			'sub_title': $(element).find('[name="sub_title"]').val(),
			'expense': $(element).find('[name="expense"]').val(),
			'category_id': $(element).find('[name="category_id"]').val(),
        	'rows': $(element).find('[name="rows"]').val(),
        	'order_field': $(element).find('[name="order_field"]').val(),
        	'order_type': $(element).find('[name="order_type"]').val(),
        	'rank': $(element).find('[name="rank"]').val(),
			'style': $(element).find('[name="style"]').val(),
        };
        return obj;
	}

	//点击提交按钮处理提交数据
	
	$('[data-rule="diy_page_submit"]').click(function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var type = $(this).data('type');

		var data = new Array();
		$('.preview-target .object-item').each(function(index,element){
			//console.log(element);
			var obj = {};
			var type = $(this).data('type');
			//根据组件类型获取数据
			switch (type) {
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

	            case ('category_nav'):
	            	obj = category_nav(element);
	            break;

	            case ('custom_text'):
	            	obj = custom_text(element);
	            break;

				case ('custom_html'):
	            	obj = custom_html(element);
	            break;

	            case ('column_list'):
	            	obj = column_list(element);
	            break;

	            case ('knowledge_list'):
	            	obj = knowledge_list(element);
	            break;

	            case ('teacher_list'):
	            	obj = teacher_list(element);
	            break;
	            case ('weixin'):
	            	obj = weixin(element);
	            break;
	            case ('member'):
	            	obj = member(element);
	            break;
	            case ('scoreshop_goods_list'):
	            	obj = scoreshop_goods_list(element);
				break;
				case ('minishop_goods_list'):
	            	obj = minishop_goods_list(element);
				break;
				case ('activity_list'):
	            	obj = activity_list(element);
	            break;
	           	case ('live_list'):
	            	obj = live_list(element);
				break;
				case ('miniprogram_live_list'):
	            	obj = miniprogram_live_list(element);
				break;
				case ('offline_list'):
	            	obj = offline_list(element);
				break;
				case ('material_list'):
	            	obj = material_list(element);
				break;
				case ('exam_paper_list'):
	            	obj = exam_paper_list(element);
				break;
				case ('article_list'):
	            	obj = article_list(element);
				break;
				case ('forum_list'):
	            	obj = forum_list(element);
				break;
				case ('category'):
	            	obj = category(element);
	            break;
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