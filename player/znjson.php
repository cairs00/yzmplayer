<?php

$api = [
    /**
     * api线路  需要标准json格式的数据接口 不支持xml！
     * 
     * 格式: '接口备注' => 你的解析接口|请求超时时间
     * 示例: 'xueren' => 'http://php.cloudhai.cn/xueren.php?url=|5' 
     * 
     * 说明: 执行顺序重上往下开始 先走你设置的第一个 失败继续往下走以此类推
     * 示例的请求超时时间的意识是 yunhai这个接口超过5秒没响应 自动请求下一个！
     * 
     * 注意: 可根据实际接口响应速度，来设置不同的超时时间，提高接口的可用性！
     * 严格按照此示例格式！否则无法使用,注意结尾逗号！
     */
     
    'A' => 'https://json.pangujiexi.com:12345/json.php?url=|5',
	
	'B' => 'http://jx.ledu8.cn/api/?key=P8QSgO61p1MpHV2ALn&url=|5',
	
	'C' => 'https://api.m3u8.tv:5678/home/api?type=ys&uid=1285201&key=bcikqtwxyADEGKUX36&url=|5',
	'D' => 'https://json.hfyrw.com/mao.go?url=|5',

'E' => 'https://player.wuenci.wang/api.php?url=|5',

'F' => 'https://cs.024zs.com:4433/api/?key=VCDHl8VpAYYG8tpBCt&url=|5',
        ];
$pz = [
    /**
     * 指定平台规则
     * 
     * 格式: '平台备注' => 域名或者标识|优先使用哪条api
     * 示例: 'qq' => 'v.qq.com|btjson-1' 
     * 
     * 说明: 示例的意思就是 请求的url中有v.qq.com的关键词
     * 就使用btjson-1 也就是 api 设置的对应的名称！
     * 
     * 注意: 严格按照此示例格式！否则无法使用,注意结尾逗号！
     */
	 
    'qq' => 'v.qq.com|A',
    
    'Iqiyi' => 'iqiyi.com|C',
    
    'Hupu' => 'hoopchina.com.cn|A',
    
    'Douyin' => 'douyin.com|B',
    
    'MGTV' => 'mgtv.com|B',
    
    'Youku' => 'youku.com|A',
    
    'PPTV' => 'pptv.com|B',

    'Le' => 'le.com|B',
    
    'Sohu' => 'sohu.com|C',
    
    'M1905' => '1905.com|C',
    
    'bilibili' => 'bilibili.com|C',
    
    'lt' => 'LT-|D',
    
    'bl' => 'bilibili.com|D',
    
    'rrm' => 'renrenmi-|D',

    'mp4' => 'mp4|C',
  
    'kF' => 'id_TcoP|D',

        ];
$url = $_REQUEST['url'];
$url == '' ? die('url为空！') : $data = pd($url,$pz,$api);
echo $data;


function pd($url,$pz,$api){
    if(count($pz)){
        foreach ($pz as $k => $v){
            $data = explode('|',$v);
            if(strstr($url,$data[0])){
                $apidata = $api[$data[1]];
                $apidatas = explode('|',$apidata);
                $r = curl($apidatas[0].$url,$apidatas[1]);
                if($r){
                    $json = json_decode($r,true);
                    if($json['code']== 200 || $json['url']!=''){
                        $json['from'] = $data[1].'-'.$k;
                        return json_encode($json);
                    }
                }
            }
        }
        return jx($api,$url);
    }else{
        return jx($api,$url);
    }
}

function jx($api,$url){
    if(count($api)){
        foreach ($api as $k => $v){
            $apidata = explode('|',$v);
            $r = curl($apidata[0].$url,$apidata[1]);
            if($r){
                $json = json_decode($r,true);
                if($json['code']== 200 || $json['url']!=''){
                    $json['from'] = $k;
                    return json_encode($json);
                }
            }
        }
        $json = [
            'code' => 404,
            'msg'  => '解析失败'
            ];
        return json_encode($json);
    }else{
        return '请检查你的api配置！';
    }
}

function curl($api)
{
    $ch = curl_init(); 
    $timeout = 10;
    $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36';
    $ip = mt_rand(11, 191) . "." . mt_rand(0, 240) . "." . mt_rand(1, 240) . "." . mt_rand(1, 240);
    curl_setopt($ch, CURLOPT_URL, $api); // 设置 Curl 目标  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Curl 请求有返回的值  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // 设置抓取超时时间  
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 跟踪重定向  
    curl_setopt($ch, CURLOPT_REFERER, ''); // 伪造来源网址  
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . $ip, 'CLIENT-IP:' . $ip)); //伪造IP  
    curl_setopt($ch, CURLOPT_USERAGENT, $ua); // 伪造ua   
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts  
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
    $content = curl_exec($ch);
    curl_close($ch); // 结束 Curl  
    return $content; // 函数返回内容
}

// 多线程请求，限超时5秒
function async_get_url($url_array, $wait_usec = 250000)
{
    $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'; // 伪造抓取 UA  
}