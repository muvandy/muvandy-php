<?php
/*
*		Muvandy API Client
*		@version 0.01a
*
*		Examples:
* 		+ Visitor Initialization:
*				$visitor = Muvandy::visitor_init($template_slug, $ip_address, $referrer, $_REQUEST, $slugs_array);	
*			+ Get version data
*				$visitor->variable_version($slug_name);
*			+ Visitor Convert
*				$visitor = Muvandy::convert($template_slug, $ip_address);
*/

/*
*		Muvandy Class
*/
class Muvandy{
	
	const BASE_URI = "http://muvandy.com";

	/*
	* Returns:
	*		MuvandyVisitor class if all ok.
	* 	false if problem encountered
	*/
	public static function visitor_init($template_slug, $ip_address, $referrer, $request = array(), $slugs = array())	{

		$post_vars = "visitor_ip=".$ip_address."&template_slug=".$template_slug."&referer=".$referrer;
		$post_vars = $post_vars."&utm_term=".$request["utm_term"]."&utm_campaign=".$request["utm_campaign"]."&utm_source=".$request["utm_source"]."&utm_medium=".$request["utm_medium"];
		
		$xml = self::post("/visitors/init.xml", $post_vars);
		if ($xml) {			
			$visitor = new MuvandyVisitor($xml);
			$visitor->fetch_visitor_values($slugs, $_REQUEST["mode"]);
			return $visitor;
		}else{
			return false;
		}
	}

	public static function get($url){
		$ch = curl_init(self::BASE_URI.$url);		
		self::curl_defaults($ch);
		$resposne = curl_exec($ch);
		curl_close($ch);
		if ($resposne) {
			$xml = new SimpleXmlElement($resposne, LIBXML_NOCDATA);
			return $xml;
		}else {
			return false;
		}
	}

	public static function post($url, $post_vars=""){
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

	public static function convert($template_slug, $ip_address){
		if (!empty($template_slug) && !empty($ip_address)){
			$xml = self::get("/templates/".$template_slug."/visitors/convert?ip_address=".$ip_address);
			if ($xml){
				$v = new MuvandyVisitor($xml);
				return $v;
			}else{
				return false;
			}
		}		
		return false;
	}

	private function curl_defaults(&$curl_obj) 	{
		curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_obj, CURLOPT_HEADER, 0);
	}

}

/*
*  Visitor class for Muvandy
*/
class MuvandyVisitor {
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
	private $variables_hash;

	public function __construct(SimpleXmlElement $xml)	{
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
					$this->source = (string) $source;
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

	public function fetch_visitor_values($slugs=array(), $mode=""){
		if ($mode != "") {
			$mode_param = "?mode=".$mode;
		} else {
			$mode_param = "";
		}
		$q_url = "/visitors/".$this->id."/variable_versions/".implode("/",$slugs).$mode_param;

		$xml = Muvandy::get($q_url);
		if ($xml){
			foreach ($xml->variable as $v){
				$this->variables_hash[(string)$v->slug] = (string)$v->version;
			}
		}
	}

	public function variable_version($key=""){
		return $this->variables_hash[$key];
	}

}


?>