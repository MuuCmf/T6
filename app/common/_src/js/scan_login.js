/**
 * 扫码登陆
 * @type {{userInfo: null, initWechatQrcode(*=): void, callback: null, hasScan(): void}}
 */
var scan_login = {
    userInfo : null,//用户信息
    callback : null,//回调
    sceneKey : null,
    apiHost  : window.location.origin,
    /**
     * @title 创建唯一ID
     */
    createSceneKey(){
        this.sceneKey =  Number(Math.random().toString().substr(2,48) + Date.now()).toString(36);
    },
    /**
     * @title 初始化二维码
     * @param elem
     */
    initWechatQrcode(elem){
        this.createSceneKey();
        let qrCodeSrc = `${this.apiHost}/channel/official/loginQrcode?scene_key=${this.sceneKey}`;
        $(elem).attr('src', qrCodeSrc);
        //查询扫码状态
        this.hasScan();
    },
    /**
     * @title 检测是否扫码
     */
    hasScan(){
        let that = this;
        $.get( `${this.apiHost}/channel/official/hasScan`,{scene_key:this.sceneKey},function (res) {
            if (res.code == 200){
                that.userInfo = res.data;
                typeof that.callback == 'function' && that.callback();
            }else{
                setTimeout(function () {
                    that.hasScan()
                },3000)
            }
        })
    },
    /**
     * @title 登录
     */
    login(){
        let that = this;
        console.log(12312312);
        $.post( `${this.apiHost}/channel/official/scanLogin`,{openid:this.userInfo.openid,scene_key:this.sceneKey},function (data) {
            if (data.code == 200) {
                toast.success(data.msg);
                if (data.data) {
                    localStorage.setItem('user_token', data.data)
                }
                if (data.url == undefined && quickLogin == "quickLogin") {
                    $('[data-role="login_info"]').append(data.msg);
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (data.url == "refresh") {
                        setTimeout(function () {
                            location.href = location.href;
                        }, 1500);
                    } else {
                        setTimeout(function () {
                            location.href = data.url;
                        }, 1500);
                    }
                }
            } else {
                toast.error(data.msg);
            }
            toast.hideLoading();
        })
    }
};