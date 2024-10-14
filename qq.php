/*
最近有点闲，打算看看网站整合 qq 如何实现的，无奈发现官方的 sdk 写得有点蛋疼，把简单的东西复杂化，看了个官网的小 demo 做了一点改写，以备不时只需。

oauth2.0 的资料详见：

http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B

Oauth类  其实就是通过请求相应的url去获得所需数据
//Step1：获取Authorization Code
//Step2：通过Authorization Code获取Access Token
//Step3：使用Access Token来获取用户的OpenID
*/
<?php 
class QQ_Oauth{
	const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
        const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
        const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";
	
	const APP_ID = "xxx";	//应用的APPKEY
	const APP_SECRET = "xxxx";//应用密钥
	const MY_URL = 'cn-php.com/lab/qq/qqconn.php';//成功授权后的回调地址


	public static function getAuthorizeUrl($app_param=array()){
	
		$params = array(
			'response_type'=>'code',
			'client_id'=>isset($app_param['appid'])?$app_param['appid']:SELF::APP_ID,
			'redirect_uri'=>isset($app_param['redirect_uri'])?$app_param['redirect_uri']:SELF::MY_URL,
			'state'=>$app_param['state'],
		
		);
		
		$authorze_url = SELF::GET_AUTH_CODE_URL.'?'.http_build_query($params);
		return $authorze_url;
	}
	
	
	public static function getToken($app_param=array()){
		$params = array(
			'grant_type'=>'authorization_code',
			'client_id'=>isset($app_param['appid'])?$app_param['appid']:SELF::APP_ID,
			'redirect_uri'=>isset($app_param['redirect_uri'])?$app_param['redirect_uri']:SELF::MY_URL,
			'client_secret'=>isset($app_param['app_secret'])?$app_param['app_secret']:SELF::APP_SECRET,
			'code'=>$app_param['code'],
		);
		$token_url = SELF::GET_ACCESS_TOKEN_URL.'?'.http_build_query($params);
		$response = file_get_contents($token_url);
		parse_str($response, $params);
	 
		$result = array(
			'access_token'=>isset($params['access_token'])?$params['access_token']:'',
		);
		 
		return $result;
    
	}
	
	public static function getOpenID($app_param=array()){
	
		$params = array(
			'access_token'=>$app_param['access_token'],
		);
		$openid_url = SELF::GET_OPENID_URL.'?'.http_build_query($params);
		$str  = file_get_contents($openid_url);
		if(strpos($str, "callback") !== false)
		{
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		}
		
	    $user = json_decode($str);
		$result = array(
			'openid'=>isset($user->openid)?$user->openid:'',
		);
		return $result;
	
	}
}

//调用代码
<?php
require './QQ_Oauth.class.php';
//Step1：获取Authorization Code
session_start();
$code = isset($_REQUEST["code"])?$_REQUEST["code"]:'';
if(empty($code))
{	
	//state参数用于防止CSRF攻击，成功授权后回调时会原样带回
        $_SESSION['state'] = md5(uniqid(rand(), TRUE)); 
	$data['state'] =  $_SESSION['state']; 
	$authorze_url = QQ_Oauth::getAuthorizeUrl($data);
	header('Location:'.$authorze_url);
}


if($_REQUEST['state'] == $_SESSION['state']) 
{
	//Step2：通过Authorization Code获取Access Token
	$data = array(
		'code'=>$code,
	);
    $token =  QQ_Oauth::getToken($data);
	//Step3：使用Access Token来获取用户的OpenID
	if(!empty($token['access_token']))
	{
		$user_info =  QQ_Oauth::getOpenID($token);
		// 通过$user_info['openid'] 去查用户表的记录
		/*
		查询数据库  uid openid 来源等等
		if(如果存在记录,已经绑定){
			//直接做登录处理
		}else{
			//跳转到绑定页面 输入用户名密码 完成绑定 更新数据库openid字段
		}
		*/
	 
	 
	}else{
		exit('token error...');
	}
}else{
	exit("csrf...");
}