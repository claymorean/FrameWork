<?php
/**
 * Created by PhpStorm.
 * User: senuer
 * Date: 2018/3/8
 * Time: 13:42
 */

namespace App\Libs;


class Curl {
    static public function getMethod($options) {
        $host = $options[ 'host' ];
        $path = isset($options[ 'path' ]) ? $options[ 'path' ] : '';
        $method = "GET";
        $query = isset($options[ 'query' ]) ? $options[ 'query' ] : [];
        $header = isset($options[ 'header' ]) ? $options[ 'header' ] : [];
//        [   //            //headers内容
//            //            //请求体类型，请根据实际请求体内容设置。
//            //            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
//            //            //请求响应体类型，部分 API 可以根据指定的响应类型来返回对应数据格式，建议手动指定此请求头，如果不设置，部分 HTTP 客户端会设置默认值 */*，导致签名错误。
//            //            "Accept: application/json",
//            //            //是否开启 Debug 模式，大小写不敏感，不设置默认关闭，一般 API 调试阶段可以打开此设置。
//            //            "X-Ca-Request-Mode: debug",
//            //            // API版本号，目前所有 API 仅支持版本号『1』，可以不设置此请求头，默认版本号为『1』。
//            //            "X-Ca-Version: 1",
//            //            //参与签名的自定义请求头，服务端将根据此配置读取请求头进行签名，此处设置不包含 Content-Type、Accept、Content-MD5、Date 请求头，这些请求头已经包含在了基础的签名结构中，详情参照请求签名说明文档。
//            //            "X-Ca-Signature-Headers: X-Ca-Request-Mode,X-Ca-Version,X-Ca-Stage,X-Ca-Key,X-Ca-Timestamp",
//            //            //请求 API的Stage，目前支持 TEST、PRE、RELEASE 三个 Stage，大小写不敏感，API 提供者可以选择发布到哪个 Stage，只有发布到指定 Stage 后 API 才可以调用，否则会提示 API 找不到或 Invalid Url。
//            //            "X-Ca-Stage: RELEASE",
//            //            //请求的 AppKey，请到 API 网关控制台生成，只有获得 API 授权后才可以调用，通过云市场等渠道购买的 API 默认已经给APP授过权，阿里云所有云产品共用一套 AppKey 体系，删除 ApppKey 请谨慎，避免影响到其他已经开通服务的云产品。
//            //            "X-Ca-Key: 60022326",
//            //            //请求的时间戳，值为当前时间的毫秒数，也就是从1970年1月1日起至今的时间转换为毫秒，时间戳有效时间为15分钟。
//            //            "X-Ca-Timestamp: ".time(),
//            //            //请求唯一标识，15分钟内 AppKey+API+Nonce 不能重复，与时间戳结合使用才能起到防重放作用。
//            //            "X-Ca-Nonce:b931bc77-645a-4299-b24b-f3669be577ac",
//            //            //请求签名。
//            //            "X-Ca-Signature: FJleSrCYPGCU7dMlLTG+UD3Bc5Elh3TV3CWHtSKh1Ys=",
//            //            //自定义请求头，此处仅作为示例，实际请求中根据 API定义可以设置多个自定义请求头。
//            //            "CustomHeader: CustomHeaderValue",
//        ];
        $url = $host.$path;
        if (!empty($query)) {
            $url .= "?";
            if (is_array($query)) {
                $flag = 1;
                foreach ($query as $key => $value) {
                    $url .= $flag == 1 ? $key.'='.$value : '&'.$key.'='.$value;
                    $flag += 1;
                }
            } else {
                $url .= $query;
            }
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (1 == strpos("$".$host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($curl);
        $result = json_decode(trim($result), true);
        try {
            curl_close($curl);
//            return urldecode(json_encode(self::encodeFormat($result))); 是字符串
            return $result;
        } catch (Exception $e) {
            return $e;
        }
    }

    static public function postMethod($options) {
        $host = $options[ 'host' ];
        $path = isset($options[ 'path' ]) ? $options[ 'path' ] : '';
        $method = "POST";
        $url = $host.$path;
        $post_data = isset($options[ 'query' ]) ? $options[ 'query' ] : [];
        $header = isset($options[ 'header' ]) ? $options[ 'header' ] : [];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // post数据
        curl_setopt($curl, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        // 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (1 == strpos("$".$host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($curl);
        $result = json_decode(trim($result), true);
        try {
            curl_close($curl);
            return $result;
        } catch (Exception $e) {
            return $e;
        }
    }
}