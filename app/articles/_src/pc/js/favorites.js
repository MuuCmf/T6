
$(function(){

    var loadFavoritesList = function(url, page, callback){

        var url = url + '?page='+page;
        $('.load-more').removeClass('hide');
        $('.load-more').html('加载中...');
        $.ajax({
            type: 'get',
            url: url,
            dataType:"json",
            success: function(ret){
                console.log(ret);

                if (ret && ret.data.data.length > 0) {
                    var html = '';

                    html += '<div class="list clearfix">';
                    var plus_html = '';
                    var thisItem = ret.data;
                    for (var i = 0, len = thisItem.data.length; i < len; i++) {
                        var n = thisItem.data[i].info;
                        console.log(n);
                        if(n.price == 0){
                        n.price = '免费';
                        }
                        plus_html += '<div class="item" data-type="'+ thisItem.data[i].info_type +'" data-id="'+ n.id +'" data-info-type="' + thisItem.data[i].info_type + '">';
                        plus_html += '<div class="image">';
                        plus_html += '<img src="'+ n.cover_200+'" />';
                        plus_html += '<div class="type-container">'+ n.type_str +'</div>';
                        plus_html += '</div>';
                        plus_html += '<div class="content">';
                        plus_html += '<div class="title h3 text-ellipsis">'+ n.title +'</div>';
                        plus_html += '<div class="description text-ellipsis">'+ n.description +'</div>';
                        plus_html += '<div class="info">';
                        plus_html += '<div class="type">';
                        if(show_view == 1){
                            plus_html += '<div class="view">';
                            plus_html += '    <i class="fa fa-eye" aria-hidden="true"></i> ' + n.view;
                            plus_html += '</div>';
                        }
                        if(show_sale == 1){
                            plus_html += '<div class="shopping">';
                            plus_html += '   <i class="fa fa-shopping-bag" aria-hidden="true"></i> ' + n.sales;
                            plus_html += ' </div>';
                        }
                        if(show_favorites == 1){
                            plus_html += ' <div class="favorites">';
                            plus_html += '   <i class="fa fa-star-o" aria-hidden="true"></i> ';
                            plus_html += '   <span class="favorites-num">' + n.favorites + '</span>'; 
                            plus_html += '</div>';
                        }
                        plus_html += '</div>';
                        plus_html += '</div>';
                        plus_html += '</div>';
                        plus_html += '</div>';
                    }

                    html += plus_html;
                    html += '</div>';
                    if(page == 1){
                    //写入DOM
                    $('.favorites-list-section').html(html);
                    }else{
                    //写入DOM
                    $('.favorites-list-section').append(html);
                    }

                    $('.load-more').addClass('hide');
                    //执行回调
                    callback();
                }else{
                    if(page == 1){//第一页无数据说明没有内容
                        $('.favorites-list-section').html(empty_html);
                        $('.load-more').addClass('hide');
                    }else{
                        $('.load-more').html('你触碰了我的底线！');
                    }
                }
                removeUnScroll();
            },
            error: function(){
                alert('加载失败');
            }
        });
    }

    window.loadFavoritesList = loadFavoritesList;
});