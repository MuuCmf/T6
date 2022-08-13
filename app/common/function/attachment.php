<?php
use think\facade\Db;
use app\common\model\Attachment;

if (!function_exists('single_image_upload')) {
    /**
     * 单图上传组件
     * @param  [type] $name      [description]
     * @param  [type] $image     [description]
     * @return [type]            [description]
     */
    function single_image_upload($name, $image, $input = false){

        $image_path = get_attachment_src($image);
        $upload_picture = '上传图片';
        $delete_picture = '删除';
        $api = url('api/file/upload');
        //兼容name数组形式
        $input_name = $name;

        $name = preg_replace('/\[.*?\]/', '', $name);
        $html = <<<EOF
        <div class="single-image-upload image-upload controls">
            <div class="upload-img-box">
                <div class="upload-pre-item popup-gallery">
    EOF;
        if(!empty($image)){
        $html .= <<<EOF
                    <div class="each">
                        <img src="{$image_path}">
                        <div class="text-center opacity del_btn"></div>
                        <div data-id="{$image}" class="text-center del_btn">{$delete_picture}</div>
                    </div>
    EOF;
        }
        $html .= <<<EOF
                </div>
            </div>
    EOF;

        if($input == false){
            $html .= <<<EOF
            <div class="input-group">
                <input type="hidden" class="form-control attach" data-name="{$name}" name="{$input_name}" value="{$image}">
                <button id="upload_single_image_{$name}" class="btn btn-default" type="button">{$upload_picture}</button>
            </div>
    EOF;
        }else{
            $html .= <<<EOF
            <div class="input-group">
                <input type="text" class="form-control attach" data-name="{$name}" name="{$input_name}" value="{$image}">
                <span class="input-group-btn">
                    <button id="upload_single_image_{$name}" class="btn btn-default" type="button">{$upload_picture}</button>
                </span>
            </div>
    EOF;
        }

        $html .= <<<EOF
        </div>
    EOF;

    $html .= <<<EOF
    <script>
        $(function () {
            var uploader_{$name}= WebUploader.create({
                // 选完文件后，是否自动上传。
                auto: true,
                // swf文件路径
                swf: 'Uploader.swf',
                // 文件接收服务端。
                server: "{$api}",
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: {id:'#upload_single_image_{$name}',multiple: false},
                // 只允许选择图片文件
                accept: {
                    title: 'Images',
                    extensions: 'gif,jpg,jpeg,bmp,png',
                    mimeTypes: 'image/*'
                }
            });
            uploader_{$name}.on('fileQueued', function (file) {
                uploader_{$name}.upload();
                toast.showLoading();
            });
            /*上传成功**/
            uploader_{$name}.on('uploadSuccess', function (file, data) {
                if (data.code) {
                    $("input[name='{$input_name}']").val(data.data.attachment);
                    $("input[name='{$input_name}']").parent().parent().find('.upload-pre-item').html(
                        '<div class="each">' +
                        '<img src="'+ data.data.url+'">' +
                        '<div class="text-center opacity del_btn"></div>' +
                        '<div data-id="'+data.data.attachment+'" class="text-center del_btn">{$delete_picture}</div>'+
                        '</div>'
                    );
                    //重启webuploader,可多次上传
                    uploader_{$name}.reset();
                } else {
                    updateAlert(data.msg);
                    setTimeout(function () {
                        $('#top-alert').find('button').click();
                        $(that).removeClass('disabled').prop('disabled', false);
                    }, 1500);
                }
            });
            //上传完成
            uploader_{$name}.on( 'uploadComplete', function( file ) {
                toast.hideLoading();
            });
            // 发生错误
            uploader_{$name}.on( 'error', function( err ) {
                console.log(err);
                if(err = 'Q_TYPE_DENIED'){
                    toast.error('不支持的文件格式');
                }
                toast.hideLoading();
            });

            //移除图片
            $('.single-image-upload').on('click','.del_btn',function(){
                var id = $(this).data('id');
                admin_image.removeImage($(this),id);
            })

        })
    </script>
    EOF;
        return $html;
    }
}

if (!function_exists('multi_image_upload')) {
    /**
     * 多图上传
     * @param  [type] $name [description]
     * @param  [type] $ids  [description]
     * @return [type]       [description]
     */
    function multi_image_upload($name, $images = '')
    {
        $upload_picture = '上传图片';
        $delete_picture = '删除';
        $picture_exists = '该图片已存在';
        $limit_exceed = '超过图片限制';
        $api = url('api/file/upload');

        $html = '';
        $html .= '
        <div class="multi-image-upload image-upload controls">
            <input class="attach" type="hidden" name="'.$name.'" value="'.$images.'"/>
            <div class="upload-img-box">
                <div class="upload-pre-item popup-gallery">';
                if(!empty($images)){
                    $aIds = explode(',',$images);
                    foreach($aIds as $aId){
                        $path = get_attachment_src($aId);
                        $html .= '
                            <div class="each">
                                <img src="'.$path.'">
                                <div class="text-center opacity del_btn"></div>
                                <div data-id="'.$aId.'" class="text-center del_btn">'.$delete_picture.'</div>
                            </div>
                        ';
                    }
                }
        
        $html .= <<<EOF
                </div>
            </div>
            <div class="input-group">
                <button id="upload_multi_image_{$name}" class="btn btn-default" type="button">{$upload_picture}</button>
            </div>
            
        </div>
        EOF;       
        $html .= <<<EOF
        <script>
        $(function () {
            var id = "#upload_multi_image_{$name}";
            var limit = parseInt(6);
            var uploader_{$name}= WebUploader.create({
                // 选完文件后，是否自动上传。
                swf: 'Uploader.swf',
                // 文件接收服务端。
                server: "{$api}",
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素
                pick: {'id': id, 'multi': true},
                fileNumLimit: limit,
                // 只允许文件。
                accept: {
                    title: 'Images',
                    extensions: 'gif,jpg,jpeg,bmp,png',
                    mimeTypes: 'image/image/jpg,image/jpeg,image/png'
                }
            });
            uploader_{$name}.on('fileQueued', function (file) {
                uploader_{$name}.upload();
                toast.showLoading();
            });
            uploader_{$name}.on('uploadFinished', function (file) {
                uploader_{$name}.reset();
            });
            /*上传成功**/
            uploader_{$name}.on('uploadSuccess', function (file, data) {
            if (data.code == 200) {
                var ids = $("input[name='{$name}']").val();
                ids = ids.split(',');
                if( ids.indexOf(data.data.attachment) == -1){
                    var rids = admin_image.upAttachVal('add',data.data.attachment, $("[name='{$name}']"));
                    if(rids.length>limit){
                        updateAlert({$limit_exceed});
                        return;
                    }
                    
                    $("input[name='{$name}']").parent().find('.upload-pre-item').append(
                        '<div class="each">'+
                        '<img src="'+ data.data.url+'">'+
                        '<div class="text-center opacity del_btn"></div>' +
                        '<div data-id="'+data.data.attachment+'" class="text-center del_btn">{$delete_picture}</div>'+
                        '</div>'
                    );
                }else{
                    updateAlert({$picture_exists});
                }
            } else {
                updateAlert(data.msg);
                setTimeout(function () {
                    $('#top-alert').find('button').click();
                    $(that).removeClass('disabled').prop('disabled', false);
                }, 1500);
            }
            });
            //上传完成
            uploader_{$name}.on( 'uploadComplete', function( file ) {
                toast.hideLoading();
            });

            //移除图片
            $('.multi-image-upload').on('click','.del_btn',function(){
                var id = $(this).data('id');
                admin_image.removeImage($(this),id);
            })
        });
        </script>
    EOF;

        return $html;
    }
}

if (!function_exists('single_file_upload')) {
    /**
     * 音频上传组件
     * @param  string $name      唯一标示
     * @param  string $audio     音频路径
     * @param  bool $input       是否显示输入框
     * @return [type]            [description]
     */
    function single_audio_upload($name, $audio, $input = false){
        $audio_path = get_attachment_src($audio);
        $upload = '上传音频';
        $delete = '删除';
        //兼容name数组形式
        $name = preg_replace('/\[.*?\]/', '', $name);
        // 获取是否启用云点播
        $vod_driver = config('extend.VOD_UPLOAD_DRIVER');
        //html 结构
        $html = <<<EOF
            <div id="upload_single_audio_{$name}" class="single-audio-upload audio-upload controls">
        EOF;

        $html .= '<div class="upload-audio-box">';
        if(!empty($audio)){
            $html .= <<<EOF
                <div class="upload-pre-item">
                    <audio id="audio_play_{$name}" controls="controls">
                        <source src="{$audio_path}" />
                    </audio>
                </div>
            EOF;
        }
        $html .= '</div>';

        $html .= '<div class="progress-box"></div>';

        if($input == false){
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                <div class="input-group">
                    <input type="hidden" class="form-control attach" name="{$name}" value="{$audio}">
                    <button class="btn btn-default btn-upload" type="button">
                        {$upload}
                        <input class="vos-upload" type="file" accept="audio/*" />
                    </button>
                </div>
                EOF;
            }else{
                $html .= <<<EOF
                <div class="input-group">
                    <input type="hidden" class="form-control attach" name="{$name}" value="{$audio}">
                    <button class="btn btn-default btn-upload" type="button">
                        {$upload}
                    </button>
                </div>
                EOF;
            }
            
        }else{
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                <div class="input-group">
                    <input type="text" class="form-control attach" name="{$name}" value="{$audio}">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-upload" type="button">
                            {$upload}
                            <input class="vos-upload" type="file" accept="audio/*" />
                        </button>
                    </span>
                </div>
                EOF;
            }else{
                $html .= <<<EOF
                <div class="input-group">
                    <input type="text" class="form-control attach" name="{$name}" value="{$audio}">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-upload" type="button">
                            {$upload}
                        </button>
                    </span>
                </div>
                EOF;
            }
        }

        $html .= <<<EOF
            </div>
        EOF;

        if($vod_driver == 'tencent'){
            // 腾讯云点播方式上传
            // 依赖 <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
            $sign_api = url('api/vod/sign');
            // 写入附件表接口
            $attachment_api = url('api/file/attachment');

            $html .= <<<EOF
            <style>
                #upload_single_audio_{$name} .btn-upload {
                    position: relative;
                }
                .vos-upload {
                    position: absolute;
                    left: 0;
                    right: 0;
                    top: 0;
                    bottom: 0;
                    opacity: 0;
                    filter: alpha(opacity=0);
                    cursor: pointer;
                }
            </style>
            <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
            <script>
                $(function () {
                    //上传按钮事件绑定
                    $('#upload_single_audio_{$name}').off('change').on('change','input[type="file"]',function(){
                        var mediaFile = this.files[0];
                        //console.log(mediaFile);
                        //云点播签名获取函数
                        function getSignature() {
                            var url = '{$sign_api}';
                            var sign = '';
                            $.ajax({
                                url: url,//请求路径
                                async: false,
                                data: '',
                                type: "POST",//GET
                                //dataType: "JSON",//需要返回JSON对象(如果Ajax返回的是手动拼接的JSON字符串,需要Key,Value都有引号)
                                success: function(resp) {
                                    //处理 resp.responseText;
                                    sign = resp;
                                },
                                error: function(a, b, c) {
                                    //a,b,c三个参数,具体请参考JQuery API
                                    alert('签名错误');
                                }
                            });
                            return sign;
                        };
                        //写入云点播本地存储表
                        function writerVodAttachment(params,type,mediaFile){
                            // 获取文件扩展名
                            var filename = mediaFile.name;
                            var index = filename.lastIndexOf(".");
                            var suffix = filename.substr(index+1);
                            // 接口路径
                            var url = '{$attachment_api}';
                            // 异步请求
                            $.ajax({
                                url: url,// 请求路径
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
                                success: function(resp) {
                                    // 写入文本框
                                    $('#upload_single_audio_{$name} input[name="{$name}"]').val(params.video.url);
                                },
                                error: function(a, b, c) {
                                    alert('写入数据错误');
                                }
                            });
                        }
                        // console.log(mediaFile);
                        // 开始上传至腾讯云点播
                        const tcVod = new TcVod.default({
                            getSignature: getSignature // 前文中所述的获取上传签名的函数
                        })
                        const uploader = tcVod.upload({
                            mediaFile: mediaFile, // 媒体文件（视频或音频或图片），类型为 File
                        })
                        // 上传完成时
                        uploader.on('media_upload', function(info) {
                            //console.log(info);
                        })
                        uploader.on('media_progress', function(info) {
                            //console.log(info.percent) // 进度
                            var percentage = info.percent; //进度值
                            var box = $('#upload_single_audio_{$name} .progress-box');
                            var percent = box.find('.progress .progress-bar');
                            // 避免重复创建
                            if (!percent.length) {
                                var html = '<div class="progress">'+
                                '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
                                '                   <span class="sr-only">0% Complete (success)</span>'+
                                '               </div>'+
                                '            </div>'+
                                '            <strong><span class="progressbar-value">0</span>%</strong>';
                                percent = $(html).appendTo(box).find('.progress-bar');
                            }
                            var progress_val = Math.round(percentage * 100);
                            percent.css('width', progress_val + '%');
                            box.find('.progressbar-value').text(progress_val);
                        })
                        uploader.done().then(function (doneResult) {
                            //console.log(doneResult);
                            //移除进度条
                            $('#upload_single_audio_{$name} .progress-box').html('');
                            //写入本地存储表
                            writerVodAttachment(doneResult,'audio',mediaFile)
                            // deal with doneResult
                        }).catch(function (err) {
                            console.log(err);
                        // deal with error
                        })
                    });
                });
            </script>
            EOF;

        }else{
            // 本地或云存储的方式上传
            $api = url('api/file/upload');
            $html .= <<<EOF
            <script>
                $(function () {
                    var uploader_{$name}= WebUploader.create({
                        // 选完文件后，是否自动上传。
                        auto: true,
                        // swf文件路径
                        swf: 'Uploader.swf',
                        // 文件接收服务端。
                        server: "{$api}",
                        // 选择文件的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: {id:'#upload_single_audio_{$name} .btn-upload',multiple: false},
                        // 只允许选择图片文件
                        accept: {
                            title: 'Audio',
                            extensions: 'mp3',
                            mimeTypes: 'audio/x-mpeg'
                        }
                    });
                    uploader_{$name}.on('fileQueued', function (file) {
                        uploader_{$name}.upload();
                        toast.showLoading();
                    });
                    /*上传成功**/
                    uploader_{$name}.on('uploadSuccess', function (file, data) {
                        if (data.code) {
                            $("#upload_single_audio_{$name} input[name='{$name}']").val(data.data.attachment);
                            $("#upload_single_audio_{$name}").find('.upload-audio-box').html(
                                '<div class="upload-pre-item">'+
                                    '<audio id="audio_play_{$name}" controls="controls">' +
                                        '<source src="'+ data.data.url+'" />' +
                                    '</audio>' +
                                '</div>'
                            );
                            //重启webuploader,可多次上传
                            uploader_{$name}.reset();
                        } else {
                            updateAlert(data.msg);
                            setTimeout(function () {
                                $('#top-alert').find('button').click();
                                $(that).removeClass('disabled').prop('disabled', false);
                            }, 1500);
                        }
                    });
                    //进度条
                    uploader_{$name}.on('uploadProgress', function( file,percentage ) {
                        var percentage = percentage; //进度值
                        var box = $('#upload_single_audio_{$name} .progress-box');
                        var percent = box.find('.progress .progress-bar');
                        //显示控制按钮
                        // 避免重复创建
                        if (!percent.length) {
                            var html = '<div class="progress">'+
                            '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
                            '                   <span class="sr-only">0% Complete (success)</span>'+
                            '               </div>'+
                            '            </div>'+
                            '            <strong><span class="progressbar-value">0</span>%</strong>';
                            percent = $(html).appendTo(box).find('.progress-bar');
                        }
                        var progress_val = Math.round(percentage * 100);
                        percent.css('width', progress_val + '%');
                        box.find('.progressbar-value').text(progress_val);
                    }),
                    //上传完成
                    uploader_{$name}.on( 'uploadComplete', function( file ) {
                        toast.hideLoading();
                        //移除进度条
                        $('#upload_single_audio_{$name} .progress-box').html('');
                    });
                    // 发生错误
                    uploader_{$name}.on( 'error', function( err ) {
                        console.log(err);
                        if(err = 'Q_TYPE_DENIED'){
                            toast.error('不支持的文件格式');
                        }
                        toast.hideLoading();
                    });
                });
            </script>
            EOF;
        }
        
        return $html;
    }
}

if (!function_exists('single_video_upload')) {
    /**
     * 视频上传
     */
    function single_video_upload($name, $video ,$input = false){

        $upload = "上传视频";
        $video_path = get_attachment_src($video);
        $api = url('api/file/upload');
        //$input_name = $name;
        //兼容name数组形式
        $name = preg_replace('/\[.*?\]/', '', $name);
        // 获取是否启用云点播
        $vod_driver = config('extend.VOD_UPLOAD_DRIVER');
        // html 结构体
        $html = <<<EOF
        <div id="upload_single_video_{$name}" class="single-video-upload video-upload controls">
            <div class="upload-video-box">
        EOF;
        if(!empty($video)){
        $html .= <<<EOF
            <div class="box-item">
                <video controls >
                    <source src="{$video_path}" ></source>
                    您的浏览器暂不支持播放该视频，请升级至最新版浏览器。
                </video>
                <div class="remove-box text-center opacity del_btn">删除</div>
            </div>
            EOF;
        }

        $html .= <<<EOF
            </div>
            <div class="progress-box"></div>
            <div class="input-group">
        EOF;
        if ($input == true){
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                    <input type="text" class="form-control attach" name="{$name}" value="{$video}">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-upload" type="button">
                            {$upload}
                            <input class="vos-upload" type="file" accept="video/*" />
                        </button>
                    </span>
                EOF;
            }else{
                $html .= <<<EOF
                    <input type="text" class="form-control attach" name="{$name}" value="{$video}">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-upload" type="button">{$upload}</button>
                    </span>
                EOF;
            }
        }else{
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                    <input type="hidden" class="form-control attach" name="{$name}" value="{$video}">
                    <button class="btn btn-default btn-upload" type="button">
                        {$upload}
                        <input class="vos-upload" type="file" accept="video/*" />
                    </button>
                EOF;
            }else{
                $html .= <<<EOF
                    <input type="hidden" class="form-control attach" name="{$name}" value="{$video}">
                    <button  class="btn btn-default btn-upload" type="button">{$upload}</button>
                EOF;
            }
        }
        $html .= <<<EOF
            </div>
        </div>
        EOF;

        // 脚本部分
        if($vod_driver == 'tencent'){
            // 腾讯云点播方式上传
            // 依赖 <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
            $sign_api = url('api/vod/sign');
            // 写入附件表接口
            $attachment_api = url('api/file/attachment');

            $html .= <<<EOF
            <style>
                #upload_single_video_{$name} .btn-upload {
                    position: relative;
                }
                .vos-upload {
                    position: absolute;
                    left: 0;
                    right: 0;
                    top: 0;
                    bottom: 0;
                    opacity: 0;
                    filter: alpha(opacity=0);
                    cursor: pointer;
                }
            </style>
            <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
            <script>
                $(function () {
                    //上传按钮事件绑定
                    $('#upload_single_video_{$name}').off('change').on('change','input[type="file"]',function(){
                        var mediaFile = this.files[0];
                        //console.log(mediaFile);
                        //云点播签名获取函数
                        function getSignature() {
                            var url = '{$sign_api}';
                            var sign = '';
                            $.ajax({
                                url: url,//请求路径
                                async: false,
                                data: '',
                                type: "POST",//GET
                                //dataType: "JSON",//需要返回JSON对象(如果Ajax返回的是手动拼接的JSON字符串,需要Key,Value都有引号)
                                success: function(resp) {
                                    //处理 resp.responseText;
                                    sign = resp;
                                },
                                error: function(a, b, c) {
                                    //a,b,c三个参数,具体请参考JQuery API
                                    alert('签名错误');
                                }
                            });
                            return sign;
                        };
                        //写入云点播本地存储表
                        function writerVodAttachment(params,type,mediaFile){
                            // 获取文件扩展名
                            var filename = mediaFile.name;
                            var index = filename.lastIndexOf(".");
                            var suffix = filename.substr(index+1);
                            // 接口路径
                            var url = '{$attachment_api}';
                            // 异步请求
                            $.ajax({
                                url: url,// 请求路径
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
                                success: function(resp) {
                                    // 写入文本框
                                    $('#upload_single_video_{$name} input[name="{$name}"]').val(params.video.url);
                                    $('#upload_single_video_{$name}').find('.upload-video-box').html(
                                        '<div class="box-item">' +
                                            '<video controls >' + 
                                                '<source src="' + params.video.url + '" ></source>' +
                                                '您的浏览器暂不支持播放该视频，请升级至最新版浏览器。' +
                                            '</video>' +
                                        '<div class="remove-box text-center opacity del_btn">删除</div>' +
                                    '</div>'
                                    );
                                },
                                error: function(a, b, c) {
                                    alert('写入数据错误');
                                }
                            });
                        }
                        // 开始上传至腾讯云点播
                        var tcVod = new TcVod.default({
                            getSignature: getSignature // 前文中所述的获取上传签名的函数
                        });
                        var uploader = tcVod.upload({
                            mediaFile: mediaFile, // 媒体文件（视频或音频或图片），类型为 File
                        });
                        // 上传完成时
                        uploader.on('media_upload', function(info) {
                            //console.log(info);
                        });
                        uploader.on('media_progress', function(info) {
                            //console.log(info.percent) // 进度
                            var percentage = info.percent; //进度值
                            var box = $('#upload_single_video_{$name} .progress-box');
                            var percent = box.find('.progress .progress-bar');
                            // 避免重复创建
                            if (!percent.length) {
                                var html = '<div class="progress">'+
                                '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
                                '                   <span class="sr-only">0% Complete (success)</span>'+
                                '               </div>'+
                                '            </div>'+
                                '            <strong><span class="progressbar-value">0</span>%</strong>';
                                percent = $(html).appendTo(box).find('.progress-bar');
                            }
                            var progress_val = Math.round(percentage * 100);
                            percent.css('width', progress_val + '%');
                            box.find('.progressbar-value').text(progress_val);
                        });
                        uploader.done().then(function (doneResult) {
                            //console.log(doneResult);
                            //移除进度条
                            $('#upload_single_video_{$name} .progress-box').html('');
                            //写入本地存储表
                            writerVodAttachment(doneResult,'video',mediaFile)
                            // deal with doneResult
                        }).catch(function (err) {
                            console.log(err);
                        // deal with error
                        });
                    });
                });
            </script>
            EOF;

        }else{
            $html .= <<<EOF
            <script>
                $(function () {
                    var uploader_{$name}= WebUploader.create({
                        // 选完文件后，是否自动上传。
                        auto: true,
                        // swf文件路径
                        swf: 'Uploader.swf',
                        // 文件接收服务端。
                        server: "{$api}",
                        // 选择文件的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: {id:'#upload_single_video_{$name} .btn-upload',multiple: false},
                        // 只允许选择图片文件
                        accept: {
                            title: 'Video',
                            extensions: 'mp4,m3u8,m4v',
                            mimeTypes: 'video/*'
                        }
                    });
                    uploader_{$name}.on('fileQueued', function (file) {
                        uploader_{$name}.upload();
                        toast.showLoading();
                    });
                    /*上传成功**/
                    uploader_{$name}.on('uploadSuccess', function (file, data) {
                        if (data.code) {
                            $("#upload_single_video_{$name} input[name='{$name}']").val(data.data.attachment);
                            $("#upload_single_video_{$name}").find('.upload-video-box').html(
                                '<div class="box-item">' +
                                        '<video controls >' + 
                                            '<source src="' + data.data.url + '" ></source>' +
                                            '您的浏览器暂不支持播放该视频，请升级至最新版浏览器。' +
                                        '</video>' +
                                    '<div class="remove-box text-center opacity del_btn">删除</div>' +
                                '</div>'
                            );
                            //重启webuploader,可多次上传
                            uploader_{$name}.reset();
                        } else {
                            updateAlert(data.msg);
                            setTimeout(function () {
                                $('#top-alert').find('button').click();
                                $(that).removeClass('disabled').prop('disabled', false);
                            }, 1500);
                        }
                    });
                    //进度条
                    uploader_{$name}.on('uploadProgress', function( file,percentage ) {
                        var percentage = percentage; //进度值
                        var box = $('#upload_single_video_{$name} .progress-box');
                        var percent = box.find('.progress .progress-bar');
                        //显示控制按钮
                        // 避免重复创建
                        if (!percent.length) {
                            var html = '<div class="progress">'+
                            '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
                            '                   <span class="sr-only">0% Complete (success)</span>'+
                            '               </div>'+
                            '            </div>'+
                            '            <strong><span class="progressbar-value">0</span>%</strong>';
                            percent = $(html).appendTo(box).find('.progress-bar');
                        }
                        var progress_val = Math.round(percentage * 100);
                        percent.css('width', progress_val + '%');
                        box.find('.progressbar-value').text(progress_val);
                    }),
                    //上传完成
                    uploader_{$name}.on( 'uploadComplete', function( file ) {
                        toast.hideLoading();
                        //移除进度条
                        $('#upload_single_video_{$name} .progress-box').html('');
                    });
                    // 发生错误
                    uploader_{$name}.on( 'error', function( err ) {
                        //console.log(err);
                        if(err = 'Q_TYPE_DENIED'){
                            toast.error('不支持的文件格式');
                        }
                        toast.hideLoading();
                    });
                    //移除
                    $('.single-video-upload').on('click','.del_btn',function(){
                        $(this).parent().parent().next().find("[name='{$name}']").val('');
                        $(this).parent().remove();
                    })
                    //视频input 改变后加载
                    $('.single-video-upload').on('blur','input[name="{$name}"]',function(){
                        var val = $(this).val();
                        if (val == ''){
                            $(this).parent().prev().children().remove();
                        }else{
                            if (val.indexOf('http') == -1){
                                val = "/attachment/" + val;
                            }
                            $(this).parent().prev().html(
                                '<div class="box-item">' +
                                        '<video controls >' + 
                                            '<source src="' + val + '" ></source>' +
                                            '您的浏览器暂不支持播放该视频，请升级至最新版浏览器。' +
                                        '</video>' +
                                    '<div class="remove-box text-center opacity del_btn">删除</div>' +
                                '</div>'
                            );
                        }
                    })
                })
            </script>
            EOF;
        }
        
        return $html;
    }
}

if (!function_exists('single_file_upload')) {
    /**
     * 文件上传组件
     * @param  string $name      唯一标示
     * @param  string $audio     音频路径
     * @param  bool $input       是否显示输入框
     * @return [type]            [description]
     */
    function single_file_upload($name, $file, $input = false){

        $file_path = get_attachment_src($file);
        $upload = '上传文件';
        $delete = '删除';
        $api = url('api/file/upload');
        //兼容name数组形式
        $name = preg_replace('/\[.*?\]/', '', $name);
        $html = <<<EOF
            <div id="upload_single_file_{$name}" class="single-file-upload file-upload controls">
        EOF;

        $html .= '<div class="upload-file-box">';
        if(!empty($file)){
        $html .= <<<EOF
            <div class="upload-pre-item">
                
            </div>
        EOF;
    }
        $html .= '</div>';
        $html .= '<div class="progress-box"></div>';
        if($input == false){
            $html .= <<<EOF
            <div class="input-group">
                <input type="hidden" class="form-control attach" name="{$name}" value="{$file}">
                <button class="btn btn-default btn-upload" type="button">{$upload}</button>
            </div>
            EOF;
        }else{
            $html .= <<<EOF
            <div class="input-group">
                <input type="text" class="form-control attach" data-name="{$name}" name="{$name}" value="{$file}">
                <span class="input-group-btn">
                    <button class="btn btn-default btn-upload" type="button">{$upload}</button>
                </span>
            </div>
    EOF;
        }

        $html .= <<<EOF
        </div>
    EOF;

    $html .= <<<EOF
    <script>
        $(function () {
            var uploader_{$name} = WebUploader.create({
                // 选完文件后，是否自动上传。
                auto: true,
                // swf文件路径
                swf: 'Uploader.swf',
                // 文件接收服务端。
                server: "{$api}",
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: {id:'#upload_single_file_{$name} .btn-upload',multiple: false},
                // 只允许选择文件
                accept: {
                    title: 'File',
                    extensions: 'md,txt,xls,xlsx,doc,docx,ppt,pptx,pdf,zip',
                    // mimeTypes: 'application/*,application/msexcel,application/msdoc'
                }
            });
            uploader_{$name}.on('fileQueued', function (file) {
                uploader_{$name}.upload();
                toast.showLoading();
            });
            //进度条
            uploader_{$name}.on('uploadProgress', function( file,percentage ) {
                var percentage = percentage; //进度值
                var box = $('#upload_single_file_{$name} .progress-box');
                var percent = box.find('.progress .progress-bar');
                //显示控制按钮
                // 避免重复创建
                if (!percent.length) {
                    var html = '<div class="progress">'+
                    '               <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
                    '                   <span class="sr-only">0% Complete (success)</span>'+
                    '               </div>'+
                    '            </div>'+
                    '            <strong><span class="progressbar-value">0</span>%</strong>';
                    percent = $(html).appendTo(box).find('.progress-bar');
                }
                var progress_val = Math.round(percentage * 100);
                percent.css('width', progress_val + '%');
                box.find('.progressbar-value').text(progress_val);
            }),
            /*上传成功**/
            uploader_{$name}.on('uploadSuccess', function (file, data) {
                if (data.code) {
                    $(".attach").val(data.data.attachment);
                    //重启webuploader,可多次上传
                    uploader_{$name}.reset();
                } else {
                    updateAlert(data.msg);
                    setTimeout(function () {
                        $('#top-alert').find('button').click();
                        $(that).removeClass('disabled').prop('disabled', false);
                    }, 1500);
                }
            });
            //上传完成
            uploader_{$name}.on( 'uploadComplete', function( file ) {
                toast.hideLoading();
                //移除进度条
                $('#upload_single_file_{$name} .progress-box').html('');
            });
            // 发生错误
            uploader_{$name}.on( 'error', function( err ) {
                console.log(err);
                if(err = 'Q_TYPE_DENIED'){
                    toast.error('不支持的文件格式');
                }
                toast.hideLoading();
            });
        });
    </script>
    EOF;
        return $html;
    }
}

if (!function_exists('get_thumb_image')) {
    /**通过ID/路径获取到图片的缩略图
     * @param        $cover_id 图片的ID
     * @param int $width 需要取得的宽
     * @param string $height 需要取得的高
     * @param bool $replace 是否强制替换
     * @return string
     * @auth 大蒙
     */
    function get_thumb_image($attachment, $width = 100, $height = 'auto', $replace = false, $type = 'attachment')
    {
        //不存在http://
        $not_http_remote=(strpos($attachment, 'http://') === false);
        //不存在https://
        $not_https_remote=(strpos($attachment, 'https://') === false);

        if ($not_http_remote && $not_https_remote) {
            $Attachment = new Attachment();
            $picture = Db::name('attachment')->where(['attachment' => $attachment])->find();
            
            if (empty($picture)) {
                return request()->domain() . '/static/common/images/nopic.png';
            }
            $attach = $Attachment->getThumbImage($picture['attachment'], $width, $height, $replace);

            return get_attachment_src($attach['src']);
            
        }else{
            return $attachment;
        }
        
    }
}

if (!function_exists('thumb')) {
    /**简写函数，等同于get_thumb_image（）
     * @param $id 图片id
     * @param int $width 宽度
     * @param string $height 高度
     * @param int $type 裁剪类型，0居中裁剪
     * @param bool $replace 裁剪
     * @return string
     */
    function thumb($attachment, $width = 100, $height = 'auto', $type = 0, $replace = false)
    {
        return get_thumb_image($attachment, $width, $height, $type, $replace);
    }
}

if (!function_exists('get_pic')) {
    /**
     * 在富文本中获取第一张图
     * @param $str_img
     * @return mixed
     */
    function get_pic($str_img)
    {
        preg_match_all("/<img.*\>/isU", $str_img, $ereg); //正则表达式把图片的整个都获取出来了
        $img = $ereg[0][0]; //图片
        $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
        preg_match_all($p, $img, $img1);
        $img_path = $img1[2][0]; //获取第一张图片路径
        return $img_path;
    }
}

if (!function_exists('get_attachment_src')) {
    /**
     * 附件路径
     * @param $path
     * @return mixed
     */
    function get_attachment_src($attachment)
    {
        //不存在http://
        $not_http_remote=(strpos($attachment, 'http://') === false);
        //不存在https://
        $not_https_remote=(strpos($attachment, 'https://') === false);

        if ($not_http_remote && $not_https_remote) {
            // 判断文件类型
            $type = 'pic';
            if(strpos($attachment, 'jpg') !== false || strpos($attachment, 'png') !== false || strpos($attachment, 'gif') !== false || strpos($attachment, 'jpeg') !== false){ 
                $type = 'pic';
            }else{
                $type = 'file';
            }
            // 初始化上传驱动
            $driver = 'local';
            // 获取上传驱动
            if($type == 'pic'){
                $driver = config('extend.PICTURE_UPLOAD_DRIVER');
            }
            if($type == 'file'){
                $driver = config('extend.FILE_UPLOAD_DRIVER');
            }
            // 获取附件路径
            if ($driver == 'local') {
                //本地url
                return request()->domain() . '/attachment/' . str_replace('//', '/', $attachment); //防止双斜杠的出现
            }
            // 阿里云OSS
            if ($driver == 'aliyun') {
                return config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '/attachment/' . $attachment;
            }
            // 腾讯云COS
            if ($driver == 'tencent') {
                return config('extend.COS_TENCENT_BUCKET_DOMAIN') . '/attachment/' . $attachment;
            }
        }else{
            return $attachment;
        }
        
    }
}

if (!function_exists('get_attachment_filename')) {
    function get_attachment_filename($attachment){
        $Attachment = new Attachment();
        $filename = $Attachment->getFileName($attachment);

        return $filename;
    }
}

if (!function_exists('get_attachment_url')) {
    /**
     * 获取本地附件目录的根Url
     * @return string
     */
    function get_attachment_url()
    {
        return request()->domain() . '/attachment/';
    }
}