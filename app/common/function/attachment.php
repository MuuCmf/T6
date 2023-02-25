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

if (!function_exists('single_audio_upload')) {
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
        // 获取是否启用云点播
        $vod_driver = config('extend.VOD_UPLOAD_DRIVER');
        //html 结构
        $html = <<<EOF
            <div id="upload_single_audio_{$name}" class="single-audio-upload audio-upload controls">
        EOF;

        $html .= <<<EOF
            <div class="progress-box"></div>
        EOF;
        $sign_api = url('api/vod/sign');
        // 写入附件表接口
        $attachment_api = url('api/file/attachment');
        if($input == false){
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                <div class="input-group">
                    <input type="hidden" name="{$name}" value="{$audio}" class="form-control attach" autocomplete="off">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="showMuuVodAudioDialog(this);" data-api="{$attachment_api}" data-sign-api="{$sign_api}">
                            {$upload}
                        </button>
                    </span>
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
                    <input type="text" name="{$name}" value="{$audio}" class="form-control attach" autocomplete="off">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="showMuuVodAudioDialog(this);" data-api="{$attachment_api}" data-sign-api="{$sign_api}">
                            {$upload}
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
            // 只触发一次
            if (!defined('MUU_VOD_AUDIO_MODAL')) {
                $html .= '
                <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
                <script type="text/javascript">
                    function showMuuVodAudioDialog(elm,options) {
                        $.muu.buildVodUploadModal(elm,"audio");
                    }
                </script>';
                define('MUU_VOD_AUDIO_MODAL', true);
            }

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
                            '            </div>';
                            percent = $(html).appendTo(box).find('.progress-bar');
                        }
                        var progress_val = Math.round(percentage * 100);
                        percent.css('width', progress_val + '%');
                        percent.text(progress_val + '%');
                    }),
                    //上传完成
                    uploader_{$name}.on( 'uploadComplete', function( file ) {
                        //移除进度条
                        $('#upload_single_audio_{$name} .progress-box').html('');
                    });
                    // 发生错误
                    uploader_{$name}.on( 'error', function( err ) {
                        console.log(err);
                        if(err = 'Q_TYPE_DENIED'){
                            toast.error('不支持的文件格式');
                        }
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
     * 视频上传(仅支持本地和云点播方式)
     */
    function single_video_upload($name, $video ,$input = false){

        $upload = "上传视频";
        // 获取视频地址
        $video_path = get_attachment_src($video);

        $api = url('api/file/upload');
        // 获取是否启用云点播
        $vod_driver = config('extend.VOD_UPLOAD_DRIVER');
        // html 结构体
        $html = <<<EOF
        <div id="upload_single_video_{$name}" class="single-video-upload video-upload controls">
        EOF;
        $html .= <<<EOF
            <div class="progress-box"></div>
        EOF;
        $sign_api = url('api/vod/sign');
        // 写入附件表接口
        $attachment_api = url('api/file/attachment');
        // 显示输入框
        if ($input == true){
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                <div class="input-group">
                    <input type="text" name="{$name}" value="{$video}" class="form-control attach" autocomplete="off">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="showMuuVodVideoDialog(this);" data-api="{$attachment_api}" data-sign-api="{$sign_api}">
                            {$upload}
                        </button>
                    </span>
                </div>
                EOF;
            }else{
                $html .= <<<EOF
                <div class="input-group">
                    <input type="text" class="form-control attach" name="{$name}" value="{$video}">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-upload" type="button">{$upload}</button>
                    </span>
                </div>
                EOF;
            }
        }else{
            // 不显示输入框
            if($vod_driver == 'tencent'){
                $html .= <<<EOF
                <div class="input-group">
                    <input type="hidden" name="{$name}" value="{$video}" class="form-control attach">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="showMuuVodVideoDialog(this);" data-api="{$attachment_api}" data-sign-api="{$sign_api}">
                            {$upload}
                        </button>
                    </span>
                </div>
                EOF;
            }else{
                $html .= <<<EOF
                <div class="input-group">
                    <input type="hidden" class="form-control attach" name="{$name}" value="{$video}">
                    <button  class="btn btn-default btn-upload" type="button">{$upload}</button>
                </div>
                EOF;
            }
        }
        $html .= <<<EOF
        </div>
        EOF;

        // 脚本部分
        if($vod_driver == 'tencent'){
            // 腾讯云点播方式上传
            // 依赖 <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
            
            // 只触发一次
            if (!defined('MUU_VOD_VIDEO_MODAL')) {
                $html .= '
                <script src="https://cdn-go.cn/cdn/vod-js-sdk-v6/latest/vod-js-sdk-v6.js"></script>
                <script type="text/javascript">
                    function showMuuVodVideoDialog(elm,options) {
                        $.muu.buildVodUploadModal(elm,"video");
                    }
                </script>';
                define('MUU_VOD_VIDEO_MODAL', true);
            }


        }else{
            // 本地上传
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
                    });
                    /*上传成功**/
                    uploader_{$name}.on('uploadSuccess', function (file, data) {
                        if (data.code) {
                            $("#upload_single_video_{$name} input[name='{$name}']").val(data.data.attachment);
                            
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
                        //移除进度条
                        $('#upload_single_video_{$name} .progress-box').html('');
                    });
                    // 发生错误
                    uploader_{$name}.on( 'error', function( err ) {
                        //console.log(err);
                        if(err = 'Q_TYPE_DENIED'){
                            toast.error('不支持的文件格式');
                        }
                    });
                    //移除
                    $('.single-video-upload').on('click','.del_btn',function(){
                        $(this).parent().parent().next().find("[name='{$name}']").val('');
                        $(this).parent().remove();
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
                    title: 'file',
                    extensions: 'md,txt,xls,xlsx,docx,doc,ppt,pptx,pdf,zip',
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
                if (data.code == 200) {
                    $(".input-group .attach[name='{$name}']").val(data.data.attachment);
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
                return request()->domain() . '\/attachment\/' . str_replace('//', '/', $attachment); //防止双斜杠的出现
            }
            // 阿里云OSS
            if ($driver == 'aliyun') {
                return config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '\/attachment\/' . $attachment;
            }
            // 腾讯云COS
            if ($driver == 'tencent') {
                return config('extend.COS_TENCENT_BUCKET_DOMAIN') . '\/attachment\/' . $attachment;
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

if (!function_exists('get_attachment_file_id')) {
    function get_attachment_file_id($attachment){
        $Attachment = new Attachment();
        $file_id = $Attachment->getFileID($attachment);

        return $file_id;
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