<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/12
 * Time: 17:07
 */

namespace App\Libs;

class WX {
    private $appID;
    private $appSecret;
    private $token;
    private $curl;
    private $redis;

    public function __construct() {
        $this->appID = env('APPID');
        $this->appSecret = env('APPSECRET');
        $this->token = env('TOKEN');
        $this->curl = new Curl();
        $this->redis = Redis::getInstance();
    }

    public function checkWeixin() {
        //微信会发送4个参数到我们的服务器后台 签名 时间戳 随机字符串 随机数
        $signature = isset($_GET[ "signature" ]) ? $_GET[ "signature" ] : "";
        $timestamp = isset($_GET[ "timestamp" ]) ? $_GET[ "timestamp" ] : "";
        $nonce = isset($_GET[ "nonce" ]) ? $_GET[ "nonce" ] : "";
//        $echostr = $_GET[ "echostr" ];

        // 1）将token、timestamp、nonce三个参数进行字典序排序
        $tmpArr = array($nonce, $this->token, $timestamp);
        sort($tmpArr, SORT_STRING);

        // 2）将三个参数字符串拼接成一个字符串进行sha1加密
        $str = implode($tmpArr);
        $sign = sha1($str);

        // 3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
        if (isset($_GET[ "echostr" ]) && $sign == $signature) {
            echo $_GET[ "echostr" ];
            exit;
        } else {
            $this->reply();
        }
    }

    public function getAccessToken() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appID."&secret=".$this->appSecret;
        //json字符串
        $json = $this->curl->httpGet($url);
        //解析json
        $obj = json_decode($json);
        if ($obj) {

            $this->redis->set('access_token', $obj->access_token, 120 * 60);

            return $obj->access_token;
        }
        return false;
    }

    public function getCode($url) {
        $scope = 'snsapi_base';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appID.'&redirect_uri='.urlencode($url).'&response_type=code&scope='.$scope.'#wechat_redirect';
        return $url;
    }

    public function getOpenID($code) {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appID.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';
        $openID = $this->curl->httpGet($url);
        $openID = json_decode($openID);
        if (isset($openID->openid)) {
            return $openID->openid;
        }
        return false;
    }

    public function getUserInfo($openid, $access_token) {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $wxUser = $this->curl->httpGet($url);
//        {
//            "subscribe": 1,
//            "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
//            "nickname": "Band",
//            "sex": 1,
//            "language": "zh_CN",
//            "city": "广州",
//            "province": "广东",
//            "country": "中国",
//            "headimgurl":"http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
//            "subscribe_time": 1382694957,
//            "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
//            "remark": "",
//            "groupid": 0,
//            "tagid_list":[128,2]
//        }
        return json_decode($wxUser);
    }

    public function setMenu($json, $access_token) {
//        $json='{
//    "button": [
//        {
//            "name": "每日策略",
//            "sub_button": [
//            {
//                "type": "click",
//                "name": "市场点评",
//                "key": "market"
//            },
//            {
//                "type": "view",
//                "name": "游资跟踪",
//                "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('APPID').'&redirect_uri='.urlencode('http://www.tongzejiaoyu.com/capital').'&response_type=code&scope=snsapi_bas
//e&state=123#wechat_redirect"
//            },
//            {
//                "type": "view",
//                "name": "行业研报",
//                "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('APPID').'&redirect_uri='.urlencode('http://www.tongzejiaoyu.com/report').'&response_type=code&scope=snsapi_bas
//e&state=123#wechat_redirect"
//            }
//            ]
//        },
//    ]
//}';
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $result = $this->curl->httpPost($url, $json);
        dd($result);
        return $this->wxResponse($result);
    }

    public function reply() {
        //1.获取到微信推送过来post数据（xml格式）
        $postArr = isset($GLOBALS[ 'HTTP_RAW_POST_DATA' ]) ? $GLOBALS[ 'HTTP_RAW_POST_DATA' ] : file_get_contents("php://input");
        //2.处理消息类型，并设置回复类型和内容
        /*<xml>
        <ToUserName><![CDATA[toUser]]></ToUserName>
        <FromUserName><![CDATA[FromUser]]></FromUserName>
        <CreateTime>123456789</CreateTime>
        <MsgType><![CDATA[event]]></MsgType>
        <Event><![CDATA[subscribe]]></Event>
        </xml>*/
//        $postObj = simplexml_load_string($postArr);
        //解析post来的XML为一个对象$postObj
        $postObj = simplexml_load_string($postArr);
        //$postObj->ToUserName = '';
        //$postObj->FromUserName = '';
        //$postObj->CreateTime = '';
        //$postObj->MsgType = '';
        //$postObj->Event = '';
        // gh_e79a177814ed
        //判断该数据包是否是订阅的事件推送
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();
        $msgType = 'text';
        switch ($postObj->MsgType) {
            case "event":
                if (strtolower($postObj->Event) == 'subscribe') {
                    $content = '欢迎关注安居平台！';
                }
                if (strtolower($postObj->Event) == 'click') {
                    switch ($postObj->EventKey) {
                        case "unbind":
                            $content = "";
                            break;
                        default:
                            $content = "欢迎关注安居平台！";
                    }
                }
                break;
            case "text":
                switch (trim($postObj->Content)) {
                    case "unbind":
                        $content = "1111111111111111111";
                        break;
                    case "locateCar":
                        $content = "22222222222222222";
                        break;
                    default:
                        $content = "您好，感谢关注“安居平台”！请问有什么需要帮助吗？";
                }
                break;
        }
        $template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
        $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        echo $info;
        exit;
    }

    /**
     * {
     * "template_list": [{
     * "template_id": "iPk5sOIt5X_flOVKn5GrTFpncEYTojx6ddbt8WYoV5s",
     * "title": "领取奖金提醒",
     * "primary_industry": "IT科技",
     * "deputy_industry": "互联网|电子商务",
     * "content": "{ {result.DATA} }\n\n领奖金额:{ {withdrawMoney.DATA} }\n领奖  时间:{ {withdrawTime.DATA} }\n银行信息:{
     * {cardInfo.DATA} }\n到账时间:  { {arrivedTime.DATA} }\n{ {remark.DATA} }",
     * "example": "您已提交领奖申请\n\n领奖金额：xxxx元\n领奖时间：2013-10-10
     * 12:22:22\n银行信息：xx银行(尾号xxxx)\n到账时间：预计xxxxxxx\n\n预计将于xxxx到达您的银行卡"
     * }]
     * }
     */
    public function getTemplate($title, $access_token) {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$access_token;
        $templates = $this->curl->httpGet($url);
        $templates = json_decode($templates);
        foreach ($templates->template_list as $template) {
            if ($template->title == $title) {
                return $template->template_id;
            }
        }
        return false;
    }

    public function pushTemplate($json, $access_token) {
//        {
//            "touser":"OPENID",
//           "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
//           "url":"http://weixin.qq.com/download",
//           "miniprogram":{
//            "appid":"xiaochengxuappid12345",
//             "pagepath":"index?foo=bar"
//           },
//           "data":{
//            "first": {
//                "value":"恭喜你购买成功！",
//                       "color":"#173177"
//                   },
//                   "keynote1":{
//                "value":"巧克力",
//                       "color":"#173177"
//                   },
//                   "keynote2": {
//                "value":"39.8元",
//                       "color":"#173177"
//                   },
//                   "keynote3": {
//                "value":"2014年9月22日",
//                       "color":"#173177"
//                   },
//                   "remark":{
//                "value":"欢迎再次购买！",
//                       "color":"#173177"
//                   }
//           }
//       }
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        return $this->curl->httpPost($url, $json);
//        {
//            "errcode":0,
//           "errmsg":"ok",
//           "msgid":200228332
//       }
    }

//    private function wxResponse($json) {
//        $json = json_decode($json);
//        if ($json->errcode == 0) {
//            return $json;
//        }
//        return false;
//    }

}