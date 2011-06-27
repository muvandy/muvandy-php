<?php
/*
*		Muvandy API Client
*		@version 0.01a
*/

/*
*  MuvandyVisitor class
*/
class MuvandyVisitor {
	
	const BASE_URI = "http://muvandy.com";
	
	public $id;
	public $template_id;
	public $referrer;
	public $fake;
	public $medium;
	public $updated_at;
	public $campaign;
	public $value;
	public $referrer_domain;
	public $converted;
	public $source;
	public $ip_address;
	public $keywords;
	public $created_at;
	public $token;
	
	private $variables_hash;
	private $tpl_slug;

	/*
	*	 Creates an instance and fetches all variable versions.
	*/
	public function __construct($tpl_slug, $api_key, $dont_fetch_vars=false) {
		$this->token = $api_key;
		$this->tpl_slug = $tpl_slug;
		if (!$dont_fetch_vars){
			$this->fetch_visitor_values();
		}
	}
	
	public function parse_visitor_from_xml(SimpleXmlElement $xml)	{
		foreach($xml as $key => $value){
			switch($key){
				case 'id': 
					$this->id = (int) $value;
					break;
				case 'template-id': 
					$this->template_id = (int) $value;
					break;
				case 'fake':
					$this->fake = (bool)$value;
					break;
				case 'converted': 
					$this->converted = (bool)$value;
					break;
				case 'keywords':
					$this->keywords = (string)$value;
					break;
				case 'medium':
					$this->medium = (string)$value;
					break;
				case 'referrer':
					$this->referrer = (string) $value;
					break;
				case "referrer-domain]":
					$this->referrer_domain = (string) $value;
					break;
				case "source":
					$this->source = (string) $value;
					break;
				case "updated-at":
					$this->updated_at = strtotime($value);	// timestamp
					break;
				case "value": 
					$this->value = (float) $value;
					break;
				case "ip-address":
					$this->ip_address = (string) $value;
					break;
				case "created-at":
					$this->created_at = strtotime($value); //timestamp
			}

		}
	}

	private function fetch_visitor_values() {		
		$xml = $this->get("/tests/".$this->tpl_slug."/visitors/variable_versions.xml?".implode("&",$this->params()));
		try {
			$this->id = (int) $xml->id;
			$variables = $xml->variable_versions->variable;
			for($i=0; $i<count($variables); $i++){
				$v = $variables[$i];
				$this->variables_hash[(string)$v->key] = (string)$v->value;
			}
		} catch (Exception $e) {
		    echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	public function variable_version($key=""){
		if (count($this->variables_hash)){
	 		return $this->variables_hash[$key];
		}
	}

	// Shorthand for variable_version() 
	public function version($key=""){
		if (!empty($key)){
			return $this->variable_version($key);
		}
	}
	
	public function version_keys() {
		if (count($this->variables_hash)){
			return array_keys($this->variables_hash);
		}
	}

	/*
	*		Parameters:
	*   	$value - A decimal/float value (required)
	*/
	public function convert($value){
		if (!floatval($value)) {
			throw new Exception("Convert require's a decimal value.");
		}
		$xml = $this->get("/tests/".$this->tpl_slug."/visitors/convert?value=".$value.'&'.implode('&',$this->params()));
		if ($xml){
			$this->parse_visitor_from_xml($xml);
		}
	}

	private function curl_defaults(&$curl_obj) 	{
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
		$ch = curl_init(self::BASE_URI.$url);		
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
		$ch = curl_init(self::BASE_URI.$url);		
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