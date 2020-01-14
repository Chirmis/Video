<?php
namespace Chrise;

class Video
{
    private $map = ['douyin','huoshan','kuaishou','chenzhongtech','weishi','pipix','xiaohongshu'];
    public function __construct()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
		if($requestMethod == 'POST'){
			$requestData = file_get_contents('php://input') ? file_get_contents('php://input') : $_POST;
			if(is_array($requestData)){
				$requestData = $requestData;
			}elseif(is_string($requestData)){
				$jsonDecode = json_decode($requestData, true);
				if(is_null($jsonDecode)){
					parse_str($requestData, $output);
					$requestData = $output;
				}else{
					$requestData = $jsonDecode;
				}
			}
		}elseif ($requestMethod == 'GET') {
			$requestData = $_GET;
        }
        if (empty($requestData)) {
            die($this->returnMsg(0, '操作失败', 'empty'));
        }else {
            die($this->go('', $requestData['url']));
        }
    }

    private function go($text='', $requestData=false)
    {
        $url = $text == '' ? $requestData : $text;
		preg_match_all("/(http|https):\/\/\S+/", $url, $res);
		$res = $res[0][0];
		$source = $this->source($res);
		if (empty($source)) {
			return $this->returnMsg(0, '暂不支持此视频', 'empty');
		}
		return $this->$source($res);
    }

    private function source($url)
	{
		$key = preg_split("/\./", $url);
		$splitNmu = count($key) -1;
		$key[0] = substr(strrchr($key[0], "/"), 1);
		$res = $key[0];
		if ($key[1] == "365yg") {
			$key[1] = 'yg';
		}
		if($key[0] == 'h5'){
			return $key[1];
		}
		if (in_array($key[$splitNmu-1], $this->map)) {
			return $key[$splitNmu-1];
		}else{
			if (in_array($res, $this->map)) {
				return $res;
			}else{
				return '';
			}
		}
	}

    private function douyin($url)
    {
        preg_match("/https:\/\/v.douyin.com\/\S+/",$url,$res);
		$params = [
			'header' => [
				'User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',	
			],
		];
		$content = $this->curl($res[0],'GET',$params);
		preg_match_all("/itemId: \"([0-9]+)\"|dytk: \"(.*)\"/", $content, $res, PREG_SET_ORDER);
		if(!$res[0][1] || !$res[1][2]){
			$data = "数据异常";
			return $this->returnMsg(0,"获取失败",$data);
		}
		$itemId = $res[0][1];
		$dytk = $res[1][2];
		$api = "https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids={$itemId}&dytk={$dytk}";
		$json = $this->curl($api, 'GET', $params);
		$arr = json_decode($json);
		$videoinfo = $arr->item_list[0]->video;
		$videourl = $this->curl($videoinfo->play_addr->url_list[0], 'GET', $params, true);
		$trueParam = [
			'ua' => "User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1",
		];
		$videourl = $this->curl($videourl,'GET',$trueParam,true);
		$data = [
			'title'    => $arr->item_list[0]->desc,
			'img'      => $videoinfo->cover->url_list[0],
			'videourl' => $videourl, 
        ];
		return $this->returnMsg(1,"获取成功",$data);
	}
	
	//此接口不稳定，随时可能失效，尽力维护(此接口包含背景音乐)
	private function douyin_other($url)
	{
		$params = [
			'postData' => [
				'action' => "douyin",
				'url'    => $url
			],
		];
		return $this->curl("http://api.osrcp.com/Api.php", 'POST', $params);
	}

	private function huoshan($url)
	{
		$he = get_headers($url,true);
		preg_match("/item_id=([0-9]+)/",$he['Location'],$id);
		$api = "https://api.huoshan.com/hotsoon/item/{$id[1]}/?live_sdk_version=745&iid=84706981861&device_id=68799984988&ac=wifi&channel=tengxun_new&aid=1112&app_name=live_stream&version_code=745&version_name=7.4.5&device_platform=android&ssmix=a&device_type=SM-G955F&device_brand=samsung&language=zh&os_api=22&os_version=5.1.1&uuid=355757010742415&openudid=4af17fcdc85f4345&manifest_version_code=745&resolution=1600*900&dpi=320&update_version_code=7458&_rticket=1567247031023&ab_version=712301%2C1138752%2C689929%2C994817%2C1121140%2C1104584%2C692223%2C889329%2C830473%2C1124665%2C662292%2C1095512%2C1039076%2C1114548%2C955277%2C1039775%2C957027%2C1103532%2C947986%2C1048436%2C1050089%2C848690%2C922888%2C1139396%2C929430%2C682009%2C478014%2C1072545%2C1108349%2C1063522%2C988865%2C1129538%2C1038565%2C1032650%2C932075%2C1050215%2C1122992%2C1132749%2C797937%2C1080023%2C956110%2C1131455%2C493890%2C1096187%2C839334%2C1017639%2C1048487%2C1138530%2C1126770%2C842000%2C1124122%2C1002040%2C1022364%2C1119160%2C1128451%2C975749%2C1133591%2C943434%2C557631%2C1030027%2C1038812%2C1124137%2C1019391%2C661942%2C1019139%2C501247%2C1032070%2C1029617%2C1069234%2C1060649%2C938227%2C915089%2C1123600%2C1122978%2C1005399%2C1092637%2C1119266%2C1046182%2C1138340%2C643982%2C1135693%2C1104206%2C1055279%2C1075140%2C1104673%2C1134135%2C768606%2C957249&client_version_code=745&jssdk_version=2.12.2.h6&mcc_mnc=46007&new_nav=1&ws_status=CONNECTED&ts=".time();
		$content = $this->curl($api, 'GET');
		$data = json_decode($content, true);
		if (empty($data['data']['video'])) {
			return $this->returnMsg(0,'获取失败','empty');
		}else {
			$data = $data['data'];
		}
		$res = [
			'title'    => $data['description'],
			'videourl' => get_headers($data['video']['url_list'][0], true)['location'],
			'img'      => $data['video']['cover_normal']['url_list'][0],
			'music'    => $data['song']['play_url']['url_list'][0]
		];
		return $this->returnMsg(1,'获取成功',$res);
	}

	private function kuaishou($url)
	{
		preg_match("/\/([\w]*)\?/", $url, $res);
		$photoId = $res[1];
		$p = "client_key=56c3713c&photoIds=$photoId";
		$s = str_replace('&','',$p).'23caab00356c';
		$s = md5($s);
		$api = 'http://api.gifshow.com/rest/n/photo/info';
		$raw = $p.'&sig='.$s;
		$params = [
			'postData' => "client_key=56c3713c&photoIds=3xpupmr6gbgn3xu&sig=b90624ae7d1877e91ac45483811b59c0",
			'header'   => [
				'Content-Type: application/x-www-form-urlencoded',
			],
			'ref' => false,
			'ua'  => 'kwai-ios',
		];
		$content = $this->curl($api, 'POST', $params);
		$data = json_decode($content, true);
		if($data['result'] != 1 || !array_key_exists('photos', $data)){
			return $this->returnMsg(0, '操作失败', 'empty');
		}
		$data = $data['photos'];
		$res = [
			'title'    => $data[0]['caption'],
			'videourl' => $data[0]['main_mv_url'],
			'img'      => $data[0]['thumbnail_url']
		];
		return $this->returnMsg(1, '操作成功', $res);
	}

	private function chenzhongtech($url)
	{
		$he = get_headers($url, true)['Location'][1];
		$he = "https:" . $he;
		return $this->go($he);
	}

	private function weishi($url)
	{
		preg_match("/feed\/([\w]*)\//", $url, $res);
		$feedid = $res[1];
		$postData = [
			'feedid'        => $feedid,
			'recommendtype' => 0,
			'datalvl'       => "all",
			'_weishi_mapExt'=> [],
		];
		$postData = json_encode($postData);
		$params = [
			'postData' => $postData,
			'ref'  => false,
			'header' => [
				"Content-Type: application/json"
			],
		];
		$json = $this->curl("https://h5.weishi.qq.com/webapp/json/weishi/WSH5GetPlayPage", 'POST', $params);
		$data = json_decode($json, true);
		if($data['ret'] !=0 || empty($data['data']['feeds'])){
			return $this->returnMsg(0, "操作失败", 'empty');
		}
		$data = $data['data']['feeds'][0];
		$res = [
			'title'    => $data['feed_desc'],
			'videourl' => $data['video_url'],
			'img'      => $data['images'][0]['url']
		];
		return $this->returnMsg(1, "操作成功", $res);
 	}

	private function pipix($url)
	{
		$he = get_headers($url, true)['Location'];
		preg_match("/item\/([0-9]+)\?/", $he, $res);
		$api = "https://h5.pipix.com/bds/webapi/item/detail/?item_id=$res[1]";
		$json = $this->curl($api);
		$data = json_decode($json, true);
		if($data['message'] != 'success'){
			return $this->returnMsg(0, "操作失败", 'empty');
		}
		$data = $data['data']['item'];
		$res = [
			'title' => $data['share']['title'],
			'videourl' => $data['video']['video_high']['url_list'][0]['url'],
			'img'      => $data['video']['cover_image']['url_list'][0]['url'],
		];
		return $this->returnMsg(1, "操作成功", $res);
	}

	//此接口不稳定，随时可能失效，尽力去维护
	private function xiaohongshu($url)
	{
		$params = [
			'postData' => [
				'action' => "xiaohongshu",
				'url'    => $url
			],
		];
		return $this->curl("http://api.osrcp.com/Api.php", 'POST', $params);
	}

    private function returnMsg($code, $msg, $data)
    {
        return json_encode(['code'=>$code, 'msg'=>$msg, 'data'=>$data]);
    }

    private function curl($url, $method='GET', $params=array(), $getinfo=false)
	{
		$ip = empty($params["ip"]) ? $this->rand_ip() : $params["ip"]; 
		$header = array('X-FORWARDED-FOR:'.$ip,'CLIENT-IP:'.$ip);
		if(isset($params["header"])){
		  $header = array_merge($header,$params["header"]);
		}
		$user_agent = empty($params["ua"]) ? 0 : $params["ua"] ;
		$ch = curl_init();                                                     
		curl_setopt($ch, CURLOPT_URL, $url);                                   
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		if($params["ref"]){
			curl_setopt($ch, CURLOPT_REFERER, $params["ref"]);
		}             
		curl_setopt($ch, CURLOPT_USERAGENT,$user_agent);                       
		curl_setopt($ch, CURLOPT_NOBODY, false);                               
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                        
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);                       
		curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                       
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);                       
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);                        
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);                           
		curl_setopt($ch, CURLOPT_ENCODING, '');                        
		if($method == 'POST'){
		  	curl_setopt($ch, CURLOPT_POST, true);               
		  	curl_setopt($ch, CURLOPT_POSTFIELDS, $params["postData"]);               
		}
		$res = curl_exec($ch);
		if ($getinfo) {
			$data = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
		}else {
			$data = $res;
		}
		curl_close($ch);                                                       
		return $data;
	}

    private function rand_ip()
	{
		$ip_long = array(
			array('607649792', '608174079'),
			array('1038614528', '1039007743'),
			array('1783627776', '1784676351'),
			array('2035023872', '2035154943'),
			array('2078801920', '2079064063'),
			array('-1950089216', '-1948778497'),
			array('-1425539072', '-1425014785'),
			array('-1236271104', '-1235419137'),
			array('-770113536', '-768606209'),
			array('-569376768', '-564133889')
		);
		$rand_key = mt_rand(0, 9);
		$ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
		return $ip;
	}
}
