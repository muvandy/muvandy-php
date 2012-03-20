<?php
/*
*		Muvandy API Client
*		@version 0.01b
*/

/*
*  Muvandy class
*/
class Muvandy {
	const API_URI = "/api/v1";
	
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
		$xml = $this->get(self::API_URI."/experiments/".$this->slug."/visitors/convert?value=".$value.'&'.implode('&',$this->params()));
		if ($xml){
			$this->parse_visitor_from_xml($xml);
		}
	}

	// Returns an array of variable keys 
	function vairable_keys() {
		if (count($this->variables_hash)){
			return array_keys($this->variables_hash);
		}
	}


	/* PUBLIC */

	// Initializes and fetches all variable versions.
	public function __construct($slug, $api_key, $skip_fetch_vars=false, $host='muvandy.com'){
		$this->base_uri = "http://".$host;
		$this->token = $api_key;
		$this->slug = $slug;
		if (!$skip_fetch_vars){
			$this->fetch_visitor_values();
		}
	}

	public function convert($value, $slug, $api_key, $host='dev.muvandy.com'){
		$mvuandy = new self($slug, $api_key, true, $host);
		$mvuandy->_convert($value);	
	}


	/* PRIVATE */

	// GET /api/v{current_version}/experiments/:slug/visitors/variable_versions.xml
	private function fetch_visitor_values(){
		$xml = $this->get(self::API_URI."/experiments/".$this->slug."/visitors/variable_versions.xml?".implode("&",$this->params()));
		try {
			// $this->id = (int) $xml->id;
			$variables = $xml->variable_versions->variable;
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

	private function client_ip() {
		if ( isset($_SERVER["REMOTE_ADDR"]) ) {
			$ip = $_SERVER["REMOTE_ADDR"] . ' ';
		} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ){
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"] . ' ';
		} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) ){
			$ip = $_SERVER["HTTP_CLIENT_IP"] . ' ';
		}
		return trim($ip);
	}

	// Sets parameters
	private function params(){
		$arr1 = array("visitor_ip" => $this->client_ip()); //, "mode" => $_REQUEST["mode"]);

		if (isset($_REQUEST["referer"])) {$arr1["referer"] = $_REQUEST["referer"];}

		if (isset($_REQUEST["mode"])){$arr1["mode"] = trim($_REQUEST["mode"]);}
		else { $arr1["mode"] = '';}

		if (isset($_REQUEST["utm_term"])){$arr1["utm_term"] = trim($_REQUEST["utm_term"]);}		
		if (isset($_REQUEST["utm_campaign"])){$arr1["utm_campaign"] = trim($_REQUEST["utm_campaign"]);}		
		if (isset($_REQUEST["utm_source"])){$arr1["utm_source"] = trim($_REQUEST["utm_source"]);}		
		if (isset($_REQUEST["utm_medium"])){$arr1["utm_medium"] = trim($_REQUEST["utm_medium"]);}		
				
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
		if ($resposne) {
			$xml = new SimpleXmlElement($resposne, LIBXML_NOCDATA);
			return $xml;
		}else {
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

?>