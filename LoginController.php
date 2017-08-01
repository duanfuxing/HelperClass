<?php

use QQ\Qqconnect;
use QQ\WeChat;

class LoginController extends Controller{

    /**
     * QQ登录-view
     */
    public function qq_login(){
        $Qqconnect = new Qqconnect();
        $Qqconnect->getAuthCode();
    }




    /**
     * QQ登录回调地址
     * return json
     */
    public function qq_callback(){
        $Qqconnect = new Qqconnect();
        $arr = $Qqconnect->getOpenId();
        $res = $Qqconnect->getInfo($arr['openid'],$arr['access_token']);

        // 登录成功   完善个人操作  res为请求到的个人信息数据
      
    }




    /**
     * 微信登录-view
     * @return mixed
     */
    public function wechat_login(){
        $weChat = new WeChat();
        $weChat->get_authorize_url();
    }




    /**
     * 微信登录回调地址
     */
    public function wechat_callback(){

        $weChat = new WeChat();
        $token = $weChat->get_access_token();
        $res = $weChat->get_userInfo($token->access_token, $token->openid);
        // 登录成功   完善个人操作  res为请求到的个人信息数据
        
    }

}
