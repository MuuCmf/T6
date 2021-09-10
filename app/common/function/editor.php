<?php

/**
 * 百度富文本编辑器
 */
function ueditor($id = 'myeditor', $name = 'content', $default='', $config='', $style='', $param='', $width='100%')
{
    $url = url("api/file/ueditor");
    if($config=='mini'){
        $config="
            toolbars:[
                    [
                        'source','|',
                        'bold',
                        'italic',
                        'underline',
                        'fontsize',
                        'forecolor',
                        'fontfamily',
                        'blockquote',
                        'backcolor','|',
                        'insertimage',
                        'insertcode',
                        'link',
                        'emotion',
                        'scrawl',
                        'wordimage'
                    ]
            ],
            autoHeightEnabled: false,
            autoFloatEnabled: false,
            initialFrameWidth: null,
            initialFrameHeight: 350
        ";
    }
    if($config == 'all') {
        $config="";
    }
    if($config == '') {
        $config="{
            autoHeightEnabled: false,
            autoFloatEnabled: false,
            initialFrameWidth: null,
            initialFrameHeight: 350
        }
        ";
    }

    $UMconfig = "{
        serverUrl :'$url',
        $config
    }";

    $tmp = '
    <script type="text/plain" name="'.$name.'" id="'.$id.'" style="'.$style.';">'.$default.'</script>
    <script type="text/javascript" charset="utf-8" src="/static/common/lib/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="/static/common/lib/ueditor/ueditor.all.min.js"></script>
    <script>
        var ue_'.$id.';
        $(function () {
            var config = '. $config .';
            ue_'.$id.' = UE.getEditor("'.$id.'", config);
        });
    </script>';

    echo $tmp;
}