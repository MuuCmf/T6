
$(function () {
    let time = Date.parse(new Date()).toString()
    let now_time = Number(time.substr(0, 10))
    let popup_time = localStorage.getItem('announcePopupTime')
    console.log(now_time, popup_time)
    // 每8小时弹出首条公告
    if (now_time - popup_time > 3600 * 8) {
        getAnnounce()
    }

    function getAnnounce() {
        $.ajax({
            url: '/api/Announce/lists.html?teminal=pc',
            type: 'get',
            dataType: 'json',
            success: function (res) {
                if (res.code == 200) {
                    let announce_list = res.data
                    console.log(announce_list)
                    if (announce_list.length > 0) {
                        let item = announce_list[0]

                        let content = ''
                        if (item.type == 1) {
                            // 图片公告
                            $('#announceModal .modal-dialog').addClass('image')
                            $('#announceModal .modal-content .modal-header').addClass('hidden')
                            $('#announceModal .modal-content .modal-footer').addClass('hidden')
                            if (item.link) {
                                let url = linkToUrl(item.link)
                                content = '<a href="' + url + '"><img src="' + item.cover_400 + '" alt=""></a>'
                            }else{
                                content = '<img src="' + item.cover_400 + '" alt="">'
                            }
                        } else {
                            // 文字公告
                            $('#announceModal .modal-dialog').addClass('text')
                            $('#announceModal .modal-content .custom-close').addClass('hidden')
                            content = item.content
                            if (item.link) {
                                let url = linkToUrl(item.link)
                                $('#announceModal .modal-content .modal-footer a').attr('href', url)
                            }else{
                                $('#announceModal .modal-content .modal-footer').addClass('hidden')
                            }
                        }

                        $('#announceModal .modal-content .modal-body .announce-content').html(content)
                        $('#announceModal').modal({
                            backdrop: false,
                            keyboard: false,
                            show: true
                        })
                        //设置弹出时间
                        //弹出时间精确到秒
                        let now_popup_time = Number(time.substr(0, 10))
                        localStorage.setItem('announcePopupTime', now_popup_time)
                    }
                }
            }
        })
    }

    function linkToUrl(link) {
        let params = link.param ? link.param : []
        let urlParams = $.param(params)
        let url = ''
        //初始化url路由地址，默认首页
        if (link.type == 'index') { //首页
            url = '/micro/index/index';
        }
        if (link.type == 'user') { //用户中心
            url = '/ucenter/config/index.html'
        }
        if (link.type == 'member') { //会员页
            url = '/ucenter/Vip/lists.html'
        }
        if (link.type == 'orders') { //订单中心
            url = '/ucenter/Orders/lists.html'
        }
        if (link.type == 'micro_page') { //自定义页面
            url = '/micro/index/index.html?' + urlParams
        }
        if (link.type == 'author_list') { //创作者列表
            url = '/ucenter/author/lists.html?' + urlParams
        }
        if (link.type == 'author_detail') { //创作者详情
            url = '/ucenter/Author/detail.html?' + urlParams
        }
        if (link.type == 'activity_activity_list') { //线下活动列表
            url = '/' + link.app + '/activity/lists.html?' + urlParams
        }
        if (link.type == 'activity_activity_detail') { //线下活动详情
            url = '/' + link.app + '/activity/detail.html?' + urlParams
        }
        if (link.type == 'articles_list') { //文章列表
            url = '/' + link.app + '/lists.html?' + urlParams
        }
        if (link.type == 'articles_detail') { //文章详情
            url = '/' + link.app + '/detail.html?' + urlParams
        }

        if (link.type == 'classroom_knowledge_list') { //点播课程列表
            url = '/' + link.app + '/knowledge/lists.html?' + urlParams
        }
        if (link.type == 'classroom_knowledge_detail') { //点播课程详情
            url = '/' + link.app + '/knowledge/detail.html?' + urlParams
        }
        if (link.type == 'classroom_column_list') { //专栏课程列表
            url = '/' + link.app + '/column/lists.html?' + urlParams
        }
        if (link.type == 'classroom_column_detail') { //专栏课程详情
            url = '/' + link.app + '/column/detail.html?' + urlParams
        }

        if (link.type == 'classroom_offline_list') { //线下课列表
            url = '/' + link.app + '/offline/lists.html?' + urlParams
        }
        if (link.type == 'classroom_offline_detail') { //线下课详情
            url = '/' + link.app + '/offline/detail.html?' + urlParams
        }
        if (link.type == 'classroom_category') { //云课堂分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'material_material_list') { //资料下载列表
            url = '/' + link.app + '/material/lists.html?' + urlParams
        }
        if (link.type == 'material_material_detail') { //资料下载详情
            url = '/' + link.app + '/material/detail.html?' + urlParams
        }
        if (link.type == 'material_category') { //资料下载分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'exam_paper_list') { //题库试卷列表
            url = '/' + link.app + '/paper/lists.html?' + urlParams
        }
        if (link.type == 'exam_paper_detail') { //题库试卷详情
            url = '/' + link.app + '/paper/detail.html?' + urlParams
        }
        if (link.type == 'exam_category') { //题库试卷分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'live_room_list') { //直播列表
            url = '/' + link.app + '/room/lists.html?' + urlParams
        }
        if (link.type == 'live_room_detail') { //直播详情
            url = '/' + link.app + '/room/detail.html?' + urlParams
        }
        if (link.type == 'live_category') { //直播分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'minishop_goods_list') { //云小店商品列表
            url = '/' + link.app + '/goods/lists?' + urlParams
        }
        if (link.type == 'minishop_goods_detail') { //云小店商品详情
            url = '/' + link.app + '//goods/detail.html?' + urlParams
        }
        if (link.type == 'minishop_category') { //云小店分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'scoreshop_goods_list') { //积分商城商品列表
            url = '/' + link.app + '/goods/lists.html?' + urlParams
        }
        if (link.type == 'scoreshop_goods_detail') { //积分商城商品详情
            url = '/' + link.app + '/pages/goods/detail?m=1' + urlParams
        }
        if (link.type == 'scoreshop_category') { //积分商城分类
            url = '/' + link.app + '/category/list.html?' + urlParams
        }

        if (link.type == 'docs_library_detail') { //知识库详情
            url = '/' + link.app + '/library/detail?app=docs' + urlParams
        }

        if (link.type == 'certificate_query_index') { //证书查询
            url = '/' + link.app + '/issue/query?app=certificate'
        }

        if (link.type == 'svideo_svideo_list') { //短视频列表
            url = '/' + link.app + '/pages/svideo/lists?m=1' + urlParams
        }

        if (link.type == 'out_url') { //外部链接至webview
            url = urlParams
        }

        return url
    }
});