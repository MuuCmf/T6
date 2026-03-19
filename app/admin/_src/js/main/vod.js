; (function ($, window, undefined) {
    'use strict';
    /**
     * 腾讯云点播客户端上传，该功能依赖腾讯云云点播客户端SDK
     * <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
     */

    /**
     * 文件上传
     * @param {File} mediaFile 媒体文件
     * @param {Function} getSignature 获取签名的函数
     * @param {Object} uploaderInfo 上传状态信息对象
     */
    var vodUpload = function (mediaFile, getSignature, uploaderInfo) {
        if (!mediaFile || !getSignature) {
            console.error('vodUpload: mediaFile and getSignature are required parameters');
            return;
        }

        const tcVod = new TcVod.default({
            getSignature: getSignature // 获取上传签名的函数
        })
        const uploader = tcVod.upload({
            mediaFile: mediaFile, // 媒体文件（视频或音频或图片），类型为 File
        })
        // 视频上传完成时
        uploader.on('media_upload', function (info) {
            console.log(info);
            if (uploaderInfo) {
                uploaderInfo.isVideoUploadSuccess = true;
            }
        })
        uploader.on('media_progress', function (info) {
            console.log(info.percent) // 进度
        })
        uploader.done().then(function (doneResult) {
            // deal with doneResult
        }).catch(function (err) {
            console.log(err);
            // deal with error
        })
    }

    /**
     * 异步获取附件云点播上传媒体资源列表数据
     * @param {*} page 
     * @param {*} type 
     * @param {*} keyword 
     */
    var getVodList = function (page, type, keyword = '') {

        keyword = keyword || '';
        // 这里仅获取云点播数据
        var url = '/api/file/lists?page=' + page + '&driver=tcvod' + '&type=' + encodeURIComponent(type) + '&keyword=' + encodeURIComponent(keyword);
        $.get(url, function (res) {
            //console.log(res);
            if (res.code) {
                // 初始化html
                var html_text = '';

                $.each(res.data.data, function (i, n) {
                    var background_image = 'background-image: url(' + n.url + ');';
                    var attachment = n.attachment;
                    var filename = n.filename;
                    var file_id = n.file_id;

                    if (type == 'image') {
                        html_text += '<div class="item" data-type="' + type + '" style="' + background_image + '" data-id=' + n.id + ' data-url="' + n.url + '" data-filename="' + filename + '" data-file-id="' + file_id + '">' +
                            '  <div class="name text-ellipsis">' + n.filename + '</div>' +
                            '  <div class="mask">' +
                            '      <i class="icon icon-check"></i>' +
                            '  </div>' +
                            '  <span class="del">' +
                            '     <span class="wi wi-delete2"></span>' +
                            '  </span>' +
                            '</div>';
                    }
                    if (type == 'audio') {

                        html_text += '<div class="item" data-type="' + type + '" data-id=' + n.id + ' data-url="' + attachment + '" data-filename="' + filename + '">' +
                            '  <img src="/static/common/images/icon-voice.png" alt="' + filename + '" />' +
                            '  <div class="time">fileID：' + n.file_id + '</div>' +
                            '  <div class="name text-ellipsis">' + n.filename + '</div>' +
                            '  <div class="mask">' +
                            '      <i class="icon icon-check"></i>' +
                            '  </div>' +
                            '  <span class="del">' +
                            '     <span class="wi wi-delete2"></span>' +
                            '  </span>' +
                            '</div>';
                    }
                    if (type == 'video') {

                        html_text += '<div class="item" data-type="' + type + '" data-id=' + n.id + ' data-url="' + attachment + '" data-filename="' + filename + '">' +
                            '  <img src="/static/common/images/icon-video.png" alt="' + filename + '" />' +
                            '  <div class="time">fileID：' + n.file_id + '</div>' +
                            '  <div class="name text-ellipsis">' + n.filename + '</div>' +
                            '  <div class="mask">' +
                            '      <i class="icon icon-check"></i>' +
                            '  </div>' +
                            '  <span class="del">' +
                            '     <span class="wi wi-delete2"></span>' +
                            '  </span>' +
                            '</div>';
                    }
                });
                $('.material-body .lists').html(html_text);
            }

            $('.pager').pager({
                page: res.data.current_page,
                recTotal: res.data.total,
                recPerPage: res.data.per_page,
                maxNavCount: 5, //最多显示按钮数量
            });
        });
    }

    /**
     * 获取附件列表
     */
    var getVodAttachment = function (ele, type) {
        //console.log(type);
        //监听对话框状态
        $('#material-' + type + '-Modal').off('shown.zui.modal').on('shown.zui.modal', function () {
            //var keyword = $('[name="keyword"]').val();
            //初始异步加载第一页
            getVodList(1, type);
        });

        //动态绑定分页器页码点击
        $('#material-' + type + '-Modal .pager').off('onPageChange').on('onPageChange', function (e, state, oldState) {
            //获取搜索关键字 - 限定在当前模态框内
            var keyword = $('#material-' + type + '-Modal [name="keyword"]').val() || '';
            if (state.page !== oldState.page) {
                getVodList(state.page, type, keyword);
            }
        });
    };

    /**
     * 构建云点播上传模态框
     * @return {[type]} [description]
     */
    var buildVodUploadModal = function (ele, type, source) {
        type = type || 'image';
        source = source || 'button';//触发源头，默认页面按钮，button 按钮触发  ueditor 编辑器内触发
        var upload_el = ''; //初始化上传HTML结构
        //console.log(ele);
        console.log(type);
        //清空内容 - 限定在当前模态框内
        $('#material-' + type + '-Modal .material-body .lists').html('<p class="loading">加载中...</p>');
        //图片类型
        if (type == 'image') {
            upload_el = '<form action="" class="form-horizontal clearfix form-inline" role="form">' +
                '               <div class="progress-box pull-left"></div>' +
                '               <div class="pull-right btn-uploader">' +
                '                  <uploader-btn> 上传图片' +
                '                  <input type="file" class="upload-btn" accept="image/*" value="上传图片" />' +
                '                  </uploader-btn>\n' +
                '               </div>\n' +
                '            </form>';
        }
        //音频类型
        if (type == 'audio') {
            upload_el = '<form action="" class="form-horizontal clearfix form-inline" role="form">' +
                '               <div class="progress-box pull-left"></div>' +
                '               <div class="pull-right btn-uploader">' +
                '                  <uploader-btn> 上传音频' +
                '                    <input type="file" class="upload-btn" accept="audio/*" value="上传音频" />' +
                '                  </uploader-btn>\n' +
                '               </div>\n' +
                '            </form>';
        }
        //视频类型
        if (type == 'video') {
            upload_el = '<form action="" method="get" class="form-horizontal clearfix form-inline" role="form">' +
                '               <div class="progress-box pull-left"></div>' +
                '               <div class="pull-right btn-uploader">' +
                '                  <uploader-btn> 上传视频' +
                '                    <input type="file" class="upload-btn" accept="video/*" value="上传视频" />' +
                '                  </uploader-btn>\n' +
                '               </div>\n' +
                '            </form>';
        }

        var modal = '<div id="material-' + type + '-Modal" class="uploader-vod-modal uploader-' + type + '-vod-modal modal fade" aria-hidden="true">\n' +
            '   <div class="modal-dialog modal-lg">\n' +
            '       <div class="modal-content ">\n' +
            '           <div class="modal-header">\n' +
            '               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
            '               <h4 class="modal-title">云点播媒体资源</h4>' +
            '           </div>\n' +
            '           <div class="modal-body material-content clearfix">\n' +
            '               <div class="material-head">';
        modal += upload_el;
        modal += '</div>\n' +
            '               <div class="material-body">' +
            '                    <div class="lists ' + type + '-container"></div>' +
            '                    <div class="attachment-page">' +
            '                       <ul id="attachmentPage" data-ride="pager" class="pager" data-elements="prev,nav,next,page_of_total_text">' +
            '                           <span class="loading">加载中...</span>' +
            '                       </ul>' +
            '                   </div>' +
            '               </div>' +
            '           </div>\n' +
            '           <div class="modal-footer" id="material-footer">\n' +
            '               <div id="btn-select">\n' +
            '                   <span class="btn btn-primary btn-submit">确认</span>\n' +
            '                   <span class="btn btn-default" data-dismiss="modal">取消</span>\n' +
            '               </div>\n' +
            '           </div>\n' +
            '       </div>\n' +
            '   </div>\n' +
            '</div>';
        //根据上传类型写入DOM，并打开模态框
        if ($("#material-" + type + "-Modal.uploader-" + type + "-vod-modal").length > 0) {
            $("#material-" + type + "-Modal.uploader-" + type + "-vod-modal").modal();
        } else {
            $('body').append(modal);
            $("#material-" + type + "-Modal.uploader-" + type + "-vod-modal").modal();
        }

        //加载附件列表
        getVodAttachment(ele, type);
        //上传按钮事件绑定
        $('.uploader-vod-modal .material-head').off('change').on('change', 'input[type="file"]', function () {
            const mediaFile = this.files[0];
            console.log(this.files);
            //云点播签名获取函数
            function getSignature() {
                var url = '/api/vod/sign';
                var sign = '';
                $.ajax({
                    url: url,//请求路径
                    async: false,
                    type: "GET",//GET
                    success: function (resp) {
                        //处理 resp.responseText;
                        sign = resp.data.signature;
                    },
                    error: function (a, b, c) {
                        alert('签名错误');
                    }
                });
                return sign;
            };
            //写入云点播附件存储表
            function writerVodAttachment(params, type, mediaFile) {
                // 获取文件扩展名
                var filename = mediaFile.name;
                var index = filename.lastIndexOf(".");
                var suffix = filename.substr(index + 1);
                //接口路径
                var url = '/api/file/attachment';
                //异步请求
                $.ajax({
                    url: url,//请求路径
                    data: {
                        'filename': mediaFile.name,
                        'attachment': params.video.url,
                        'type': type, // 附件类型
                        'mime': mediaFile.type,
                        'size': mediaFile.size,
                        'ext': suffix,
                        'driver': 'tcvod',
                        'file_id': params.fileId,
                    },
                    type: "POST",//GET
                    success: function (resp) {
                        //初始加载第一页
                        getVodList(1, type);
                    },
                    error: function (a, b, c) {
                        alert('写入数据错误');
                    }
                });
            }

            //开始上传至腾讯云点播
            const tcVod = new TcVod.default({
                getSignature: getSignature, // 前文中所述的获取上传签名的函数
            })
            const uploader = tcVod.upload({
                mediaFile: mediaFile, // 媒体文件（视频或音频或图片），类型为 File
            })

            // 视频上传完成时
            uploader.on('media_upload', function (info) {
                //console.log(info);
            })
            uploader.on('media_progress', function (info) {
                //console.log(info.percent) // 进度
                var percentage = info.percent; //进度值
                var $box = $('.material-head .progress-box'),
                    $percent = $box.find('.progress .progress-bar');
                // 避免重复创建
                if (!$percent.length) {
                    var html = '<div class="progress">' +
                        '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">' +
                        '                   <span class="sr-only">0% Complete (success)</span>' +
                        '               </div>' +
                        '            </div>' +
                        '            <strong><span class="progressbar-value">0</span>%</strong>';
                    $percent = $(html).appendTo($box).find('.progress-bar');
                }
                var progress_val = Math.round(percentage * 100);
                $percent.css('width', progress_val + '%');
                $box.find('.progressbar-value').text(progress_val);
            })
            uploader.done().then(function (doneResult) {
                //console.log(doneResult);
                //移除进度条
                $('.material-head .progress-box').html('');

                // 检查doneResult结构是否正确
                if (doneResult && doneResult.video && doneResult.video.url && doneResult.fileId) {
                    //写入本地存储表
                    writerVodAttachment(doneResult, type, mediaFile);
                } else {
                    console.error('Invalid doneResult structure:', doneResult);
                    alert('上传成功，但返回数据格式错误');
                }
                // deal with doneResult
            }).catch(function (err) {
                console.error('Upload error:', err);
                alert('上传失败: ' + (err.message || '未知错误'));
                // deal with error
            })
        });

        //列表元素点击事件绑定
        $('.uploader-vod-modal .material-body').off('click').on('click', '.item', function (event) {
            //event.stopImmediatePropagation();
            var tagname = event.target.tagName.toLowerCase();
            var _this = $(this);
            if (tagname == 'span') {

                if (confirm("确认删除该资源？") == true) {
                    //执行删除
                    var id = _this.data('id');
                    //内容删除接口地址
                    var url = '/admin/attachment/del';
                    //异步请求
                    $.ajax({
                        url: url,
                        data: {
                            'id': id,
                        },
                        type: "POST",//GET
                        success: function (resp) {
                            //初始加载第一页
                            getVodList(1, type);
                        },
                        error: function (a, b, c) {
                            alert('删除失败');
                        }
                    });
                }

            } else {
                //选中该元素
                if (_this.hasClass('active')) {
                    _this.find('.mask').css('display', 'none');
                    _this.removeClass('active');
                } else {
                    _this.find('.mask').css('display', 'block');
                    _this.addClass('active');
                }
            }
        });

        //确认按钮点击
        $('.uploader-vod-modal .modal-footer').off('click').on('click', '.btn-submit', function (event) {
            event.stopImmediatePropagation();
            //表单内写入
            var item_url = '';
            $('.uploader-vod-modal .lists .item.active').each(function () {
                var _this = $(this);
                //表单内
                item_url = _this.data('url');
            });
            //表单内
            //console.log(item_url);
            var input_group = $(ele).parent().parent();
            //图片类附件写入
            if (type == 'image') {
                input_group.next().find('img').attr('src', item_url);
            }
            //写入路径
            input_group.find('input.attach').val(item_url);

            //关闭模态框
            $(this).parents('.uploader-vod-modal').find('[data-dismiss="modal"]').click();
        });
    };

    $.muu.vodUpload = vodUpload;
    $.muu.buildVodUploadModal = buildVodUploadModal; //云点播上传模态

}(jQuery, window, undefined));

