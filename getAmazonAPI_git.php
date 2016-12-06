<?php
 
ItemLookup('itemNum');
 
function ItemLookup ($id)
{
    $params = array();
 
    // 必須
    $access_key_id = 'アクセスキー';
    $secret_access_key = 'シークレット';
    $params['AssociateTag'] = 'アソシエイトタグ';
    $baseurl = 'http://ecs.amazonaws.jp/onca/xml';
 
    // パラメータ
    $params['Service'] = 'AWSECommerceService';
    $params['AWSAccessKeyId'] = $access_key_id;
    $params['Version'] = '2011-08-01';
    $params['Operation'] = 'ItemLookup';
    $params['ItemId'] = $id;
    //$params['ResponseGroup'] = 'ItemAttributes';
    $params['ResponseGroup'] = 'Reviews';     
    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    ksort($params);
 
    // 送信用URL・シグネチャ作成
    $canonical_string = '';
    foreach ($params as $k => $v) {
        $canonical_string .= '&' . urlencode_rfc3986($k) . '=' . urlencode_rfc3986($v);
    }
    $canonical_string = substr($canonical_string, 1);
    $parsed_url = parse_url($baseurl);
    $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $secret_access_key, true));
    $url = $baseurl . '?' . $canonical_string . '&Signature=' . urlencode_rfc3986($signature);
 
    // xml取得
    $xml = request($url);
 
    $needle = "http://www.amazon.jp";
    $urlPosi = mb_strpos_all($xml, $needle);            
    $xml = substr($xml, $urlPosi[0]);
    $pos = strpos($xml, 'true');
        
    if ($pos === false) {
        $posF = strpos($xml, 'false');
        echo substr($xml, 0, $posF);
    } else {
        echo substr($xml, 0, $pos);
    }
}

function mb_strpos_all($haystack, $needle, $offset=0, $encoding=null, $result=array() ){
    if( empty($encoding) ) $encoding = mb_internal_encoding();
    $pos = mb_strpos($haystack, $needle, $offset, $encoding);
    if($pos !== false){
        $result[] = $pos;
        return mb_strpos_all($haystack, $needle, $pos + 1, $encoding, $result);
    } else {
        return $result;
    }
}

function urlencode_rfc3986($str)
{
    return str_replace('%7E', '~', rawurlencode($str));
}
 
function request($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $response = curl_exec($ch);
    curl_close($ch);
 
    return $response;
    //return simplexml_load_string($response); //オブジェクトとして返す場合
}

?>