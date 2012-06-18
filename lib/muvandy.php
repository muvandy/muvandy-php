<?php
/*
*		Muvandy API Client
*		@version 1.0.2a1
*/

/*
*  Muvandy class
*/


class Muvandy {
	const API_URI = "/api/v1";
	const CK_PREFIX = "MVNDY-";
	
	public $id;  // visitor $id

	public $token;
	private $variables_hash;
	private $slug;
	private $base_uri;
	


	function get_variation($key="", $default=""){
		if (!empty($key)){
			$output = $this->variable_version($key);
			if (empty($output)){ $output = $default; }
			return $output;
		}
	}

	// Parameters:
	// 	$value - A decimal/float value (required)
	function _convert($value){
		if (!floatval($value)) {
			throw new Exception("Convert require's a decimal value.");
		}
		$xml = $this->post(self::API_URI."/experiments/".$this->slug."/visitors/convert.xml", "value=".$value.'&'.implode('&',$this->params()));
		if ($xml){
			$this->parse_visitor_from_xml($xml);
		}
	}

	// Returns an array of variable keys 
	function vairable_keys() {
		if (count($this->variables_hash)){
			return array_keys($this->variables_hash);
		} else {
			return array();
		}
	}

	/* PUBLIC */

	// Initializes and fetches all variable versions.
	public function __construct($slug, $api_key, $visitor_key, $params = array() ){
		// Host override
		if (isset($params["host"])){
			$host = $params["host"];
		}else {
			$host='api.muvandy.com';
		}
		$this->base_uri = "http://".$host;
		$this->token = $api_key;
		$this->slug = $slug;
		$this->visitor_key = $visitor_key;
		if (isset($params['segment_by'])){
			$this->segment_name = $params['segment_by'];
		}
		
		if (!isset($params["skip_fetch_vars"])){
			$this->fetch_visitor_values();
		}
	}

	public function convert($value, $slug, $api_key, $virtual_key, $params=array() ){
		$params["skip_fetch_vars"] = true;
		$mvuandy = new self($slug, $api_key, $virtual_key, $params);
		$mvuandy->_convert($value);	
	}

	public static function client_ip() {
		if ( isset($_SERVER["REMOTE_ADDR"]) ) {
			$ip = $_SERVER["REMOTE_ADDR"] . ' ';
		} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ){
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"] . ' ';
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) ){
			$ip = $_SERVER["HTTP_CLIENT_IP"] . ' ';
		}
		return trim($ip);
	}

	public static function ini_track_info() {
		$expires_in = time()+60*60*24*30;
		
		if (!empty($_SERVER["HTTP_REFERER"]) ) { 
			if (isset($_COOKIE[self::CK_PREFIX.'referer'])) { setcookie(self::CK_PREFIX.'referer', '', time()-3600); }
			setcookie(self::CK_PREFIX.'referer', $_SERVER["HTTP_REFERER"], $expires_in); 
		}
		
		foreach (Muvandy::track_vars() as $var) {
			if (!empty($_REQUEST[$var])) {
				if (isset($_COOKIE[$var])) { setcookie(self::CK_PREFIX.$var, '', time()-3600);}
				setcookie(self::CK_PREFIX.$var, trim($_REQUEST[$var]), time()+2592000);
			}
		}
	}

	public static function track_vars() {
		return array("utm_term", "utm_campaign", "utm_source", "utm_medium");
	}

	/* PRIVATE */

	// GET /api/v{current_version}/experiments/:slug/visitors/variable_variations.xml
	private function fetch_visitor_values(){
		$xml = $this->get(self::API_URI."/experiments/".$this->slug."/visitors/variable_variations.xml?".implode("&",$this->params()));

		try {
			// $this->id = (int) $xml->id;
			$variables = $xml->variable_variations->variable;
			for($i=0; $i<count($variables); $i++){
				$v = $variables[$i];
				$this->variables_hash[(string)$v->key] = (string)$v->value;
			}
		} catch (Exception $e) {
		    echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	private function parse_visitor_from_xml(SimpleXmlElement $xml){
		foreach($xml as $key => $value){
			switch($key){
				case 'id': 
					$this->id = (int) $value;
					break;
			}

		}
	}

	// Get value from $variables_hash
	private function variable_version($key=""){
		if (count($this->variables_hash)){
	 		return $this->variables_hash[$key];
		}
	}

	private function curl_defaults(&$curl_obj){
		curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_obj, CURLOPT_HEADER, 0);
		curl_setopt($curl_obj, CURLOPT_USERPWD, $this->token.':');
	}

	// Sets parameters
	private function params(){
		$arr1 = array("visitor_key" => $this->visitor_key); //, "mode" => $_REQUEST["mode"]);
		
		if (isset($this->segment_name)) { $arr1['segment_name'] = $this->segment_name; }
		if (isset($_COOKIE[self::CK_PREFIX."referer"])) $arr1["referer"] = $_COOKIE[self::CK_PREFIX."referer"];
		else if (isset($_SERVER["HTTP_REFERER"])) $arr1["referer"] = $_SERVER["HTTP_REFERER"];

		if (isset($_REQUEST["mode"])){$arr1["mode"] = trim($_REQUEST["mode"]);}

		foreach (Muvandy::track_vars() as $var) {
			$var_in_cookie = self::CK_PREFIX.$var;
			if (isset($_COOKIE[$var_in_cookie])) { $arr1[$var] = $_COOKIE[$var_in_cookie]; }
			else if (isset($_REQUEST[$var])) { $arr1[$var] = $_REQUEST[$var]; }
		}	
				
		$arr2 = array();
		foreach ($arr1 as $key => $value) {
	    $arr2[] = "$key=$value";
		}
		return $arr2;
	}

	private function get($url){
		$ch = curl_init($this->base_uri.$url);		
		$this->curl_defaults($ch);
		$resposne = curl_exec($ch);
		curl_close($ch);
		try {
			if ($resposne) {
				$xml = new SimpleXmlElement($resposne, LIBXML_NOCDATA);
				return $xml;
			}else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}

	private function post($url, $post_vars=""){
		$ch = curl_init($this->base_uri.$url);		
		self::curl_defaults($ch);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
		$response = curl_exec($ch);
		if ($response){
			$xml = new SimpleXmlElement($response, LIBXML_NOCDATA);
			return $xml;
		}else{
			return false;
		}
	}

}

Muvandy::ini_track_info();

?>