<?php

namespace Wx\WeChatShare;


class WeChatShare{
    private $appId;
    private $appSecret;
    private $ticket_name;
    private $access_token;
    private $current_url;

    /**
     * 实例化
     * WeChatShare constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId, $appSecret,$current_url) {

        if(empty($appId) || empty($appSecret) || empty($current_url)){
            return false;
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->ticket_name = strtoupper(sha1($appId.'_ticket'));
        $this->access_token = strtoupper(sha1($appId.'_access_token'));
        $this->current_url = $current_url;
    }




    /**
     * 获取分享签名
     * @return array
     */
    public function getSignPackage() {

        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
//        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
//        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url = $this->current_url;

        $timestamp = time();

        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";


        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );

        return $signPackage;
    }





    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }





    /**
     * 生成ticket字符串
     * @return mixed
     */
    private function getJsApiTicket() {

        if(!S($this->ticket_name)){

            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            if ($res->ticket) {
                S($this->ticket_name,$res->ticket,7000);
            }

        }

        return S($this->ticket_name);

    }






    /**
     * 生成access_token字符串
     * @return mixed
     */
    private function getAccessToken() {

        if(!S($this->access_token)){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            if ($res->access_token) {
                S($this->access_token,$res->access_token,7000);
            }

        }

        return S($this->access_token);

    }






    /**
     * 发送http请求
     * @param $url
     * @return mixed
     */
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}
