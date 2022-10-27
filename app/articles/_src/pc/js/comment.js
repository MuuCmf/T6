$(function(){

	var loadCommentList = function(url, data, page, callback){
		
		var url = url + '&page='+page;
        var loading_html = '<div class="loading">加载中...</div>';
		var empty_html = '<div class="empty">还没有评论哦！</div>';
        $('[data-role="list-section"]').html(loading_html);
		$.ajax({
			type: 'get',
			url: url,
			data: data,
			dataType:"json",
			success: function(ret){
                //console.log(ret);
			    if (ret && ret.data.data.length > 0) {
			        var html = '';

			        html += '<div class="list clearfix">';
			        var item_html = '';
			        var thisItem = ret.data;
			        for (var i = 0, len = thisItem.data.length; i < len; i++) {
			            var n = thisItem.data[i];

			            item_html += '<div class="item clearfix" data-type="comment_item" data-id="'+ n.id +'">';
                            item_html += '<div class="avatar">';
                                item_html += '<img src="'+ n.user_info.avatar64 +'" />';
                            item_html += '</div>';
                            item_html += '<div class="content">';
                                item_html += '<div class="nickname">'+ n.user_info.nickname +'</div>';
                                item_html += '<div class="comment-detail">';
									item_html += '<p>' + n.content + '</p>';
                                    item_html +='<div class="comment-bottom">';
                                    item_html += '<div class="time">' + n.create_time_friendly_str + '</div>';
									if(n.support_yesno == 1){
										item_html += '<div class="support active" data-id="'+ n.id +'">';
										item_html += '<i class="fa fa-heart" aria-hidden="true"></i>';
                                        if(n.support == 0){
                                            item_html += '<p>赞</p>';
                                        }else{
                                            item_html += '<p>' + n.support + '</p>';
                                        }
										item_html += '</div>';
									}else{
										item_html += '<div class="support" data-id="'+ n.id +'">';
										item_html += '<i class="fa fa-heart-o" aria-hidden="true"></i>';
                                        if(n.support == 0){
                                            item_html += '<p>赞</p>';
                                        }else{
                                            item_html += '<p>' + n.support + '</p>';
                                        }
										item_html += '</div>';
									}
                                    item_html += '</div>';
                                item_html += '</div>';
                            item_html += '</div>';
			            item_html += '</div>';
			        }
					// item_html += '<div class="time mui-ellipsis">'+ n.create_time_friendly_str +'</div>';
			        html += item_html;
                    html += '</div>';
                    
                    $('[data-role="list-section"]').html(html);
					//console.log(thisItem);
					$('[data-role="list-section"] .title .total').text('('+thisItem.total+')');
			  	}else{
			  		if(page == 1){
                        //第一页无数据说明没有内容
			  			$('[data-role="list-section"]').html(empty_html);
			  		}
                }

                //执行回调
				callback(ret);
			},
			error: function(){
				//alert('加载失败');
			}
		});
	}

	window.loadCommentList = loadCommentList;

});