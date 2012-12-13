<?php
class gridology_ru implements SocialLoginPlugin {
	public static function login( $code ) {
		global $wgGesSecret, $wgGesAppId;
		$r = SLgetContents("http://gridology.ru/oauth/token", array(
			"redirect_uri" => SpecialPage::getTitleFor('SocialLogin')->getCanonicalURL() . "?action=login&service=gridology.ru",
			"client_id" => $wgGesAppId,
			"client_secret" => $wgGesSecret,
			"grant_type" => "authorization_code",
			"code" => $code
		));
		$response = json_decode($r);
		if (!isset($response->access_token)) return false;
		$access_token = $response->access_token;
		$r = SLgetContents("http://gridology.ru/api/me.json?access_token=$access_token");
		$response = json_decode($r);
		$id = $response->id;
		$e = explode("@", $response->email);
		$e = $e[0];
		$name = SLgenerateName(array($e, $response->name));
		$_SESSION['sl_token']=$access_token;
		return array(
			"id" => $id,
			"service" => "gridology.ru",
			"profile" => "$id@gridology.ru",
			"name" => $name,
			"email" => $response->email,
			"realname" => $response->name
		);
	}

	public static function check( $id ) {
	    $access_token=$_SESSION['sl_token'];
		$r = SLgetContents("http://gridology.ru/api/me.json?access_token=$access_token");
		$response = json_decode($r);
		if (!isset($response->id) || $response->id != $id) return false;
		else return array(
			"id" => $id,
			"service" => "gridology.ru",
			"profile" => "$id@gridology.ru",
			"realname" => $response->name,
			"access_token" => $access_token
		);
	}
	
	public static function loginUrl( ) {
		global $wgGesAppId;
		return "http://gridology.ru/oauth/authorize?client_id=$wgGesAppId&display=page&response_type=code&redirect_uri=" . urlencode(SpecialPage::getTitleFor('SocialLogin')->getCanonicalURL() . "?action=login&service=gridology.ru");
	}
}
