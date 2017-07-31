<?php
/**
 * 微信登录类
 */
namespace QQ\WeChat;

class WeChat {

    private $appId = '';
    private $appSecret = '';
    private $redirect_uri = '';

    public function __construct(){
        $this->appId        = '你的APPID';
        $this->appSecret    = '你的APPKEY';
        $this->redirect_uri = '你的回调地址';
    }




    /**
     * 获取微信授权网址
     * @return string
     */
    public function get_authorize_url(){
        $state = md5(uniqid(rand(), TRUE));
        $url = "https://open.weixin.qq.com/connect/qrconnect";
        $param['appid']         = $this->appId;
        $param['redirect_uri']  = $this->redirect_uri;
        $param['response_type'] = "code";
        $param['scope']         = "snsapi_login";
        $param['state']         = $state;
        $param                  = http_build_query($param,'','&');
        $authorize_url          = $url."?".$param.'#wechat_redirect';
        header("Location:".$authorize_url);
    }




    /**
     * 根据code获取授权toke
     * @return mixed
     */
    public function get_access_token(){
        if(!isset($_GET['code'])){
            exit("错误代码：300001");
        }

        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$_GET['code'].'&grant_type=authorization_code';
        $res = $this->https_request($token_url);
        return json_decode($res);
    }




    /**
     * 根据access_token以及openid获取用户信息
     * @param $access_token
     * @param $openId
     * @return mixed
     */
    public function get_userInfo($access_token,$openId){
        $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openId;
        $res = $this->https_request($info_url);
        return json_decode($res);
    }




    /**
     * https请求
     * @param $url
     * @param null $data
     * @return mixed
     */
    public function https_request($url , $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}
