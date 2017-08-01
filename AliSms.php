<?php

namespace Helper\AliSms;

/**
 * 阿里云短信发送类
 *
 * Class Alisms
 * @package Org\Zdy
 * @author Duan
 *
 * 使用方式： 1）实例化此类（签名，模版ID） 可以随意使用你的申请好的短信签名和模板
 *           2）调用send_verify（电话号，内容）
 * 参数说明：电话号：可以同时发送多个，逗号分隔最多20个
 *          内容：TplJsonVariable[你在阿里云设置的变量名] 固定格式这么写   兼容你一条短信中有多个变量
 */

class AliSms {


    private $accessKeyId;      //申请的AKID
    private $accessKeySecret;  //申请的AKSecret
    private $signName;         //申请的签名
    private $templateCode ;    //申请的模版ID


    /**
     * 初始化参数
     * Alisms constructor.
     * @param $signName
     * @param $templateCode
     */
    public function __construct($signName,$templateCode) {
        $cofig = array (
            'accessKeyId' => '你的AKID',
            'accessKeySecret' => '你的AKSecret',
            'signName' => $signName ?? '默认的签名',
            'templateCode' => $templateCode ?? '默认的模板ID'
        );
        // 配置参数
        $this->accessKeyId = $cofig ['accessKeyId'];
        $this->accessKeySecret = $cofig ['accessKeySecret'];
        $this->signName = $cofig ['signName'];
        $this->templateCode = $cofig ['templateCode'];
    }



    /**
     * 发送短信
     * @param $mobile
     * @param $TplVariable
     * @return bool
     */
    public function send_verify($mobile,$TplVariable) {

        $params = array (
            'SignName'          => $this->signName,
            'Format'            => 'JSON',
            'Version'           => '2017-05-25',
            'AccessKeyId'       => $this->accessKeyId,
            'SignatureVersion'  => '1.0',
            'SignatureMethod'   => 'HMAC-SHA1',
            'SignatureNonce'    => uniqid (),
            'Timestamp'         => gmdate ( 'Y-m-d\TH:i:s\Z' ),
            'Action'            => 'SendSms',
            'TemplateCode'      => $this->templateCode,
            'PhoneNumbers'      => $mobile,
//            'TemplateParam'     => '{"content":"' . $content . '"}'    //更换为自己的实际模版，单个变量长度不超过15个字符
            'TemplateParam'     => json_encode($TplVariable,JSON_UNESCAPED_UNICODE)
        );
        // 计算签名并把签名结果加入请求参数
        $params ['Signature'] = $this->computeSignature ( $params, $this->accessKeySecret );

        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query ( $params );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        return json_decode ( $result, true );
    }



    /**
     * 替换签名
     * @param $string
     * @return mixed|string
     */
    private function percentEncode($string) {
        $string = urlencode ( $string );
        $string = preg_replace ( '/\+/', '%20', $string );
        $string = preg_replace ( '/\*/', '%2A', $string );
        $string = preg_replace ( '/%7E/', '~', $string );
        return $string;
    }



    /**
     * 计算签名
     * @param $parameters
     * @param $accessKeySecret
     * @return string
     */
    private function computeSignature($parameters, $accessKeySecret) {
        ksort ( $parameters );
        $canonicalizedQueryString = '';
        foreach ( $parameters as $key => $value ) {
            $canonicalizedQueryString .= '&' . $this->percentEncode ( $key ) . '=' . $this->percentEncode ( $value );
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode ( substr ( $canonicalizedQueryString, 1 ) );
        $signature = base64_encode ( hash_hmac ( 'sha1', $stringToSign, $accessKeySecret . '&', true ) );
        return $signature;
    }

}
