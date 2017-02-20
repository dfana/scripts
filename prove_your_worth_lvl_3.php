<?php

abstract class Method{
    const GET = 0;
    const POST = 1;
}

class cURLRequest {

	private $ch;
	private $response = false;
	private $url;
	private $method;
	private $header;
	private $body;
	
	public function __construct($url, $method, $keepSessionAlive = false,
								array $options = array(), 
								array $header = array() ){
		$this->response = false;
		$this->url = $url;
		$this->method = $method;
		$this->ch = curl_init();
		$this->setOptions($options);
		$this->setHeaders($header);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
		
	 
		if($keepSessionAlive){
			$this->keepSession();
		}
	}
 
	
	public function setReferer($referer){
		curl_setopt($this->ch, CURLOPT_REFERER, $referer);
	}
	
	public function setConfig($url, $method, array $options = array(), 
								array $header = array(),$isBinary = false){
		$this->response = false;
		$this->url = $url;
		$this->method = $method;
		$this->setOptions($options);
		$this->setHeaders($header); 
		if($isBinary){
			$this->isBinary();
		}
	}
	
	public function keepSession(){
		$cookiestore=tempnam( sys_get_temp_dir(), '_cookiejar_' );
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookiestore); 
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookiestore); 
	}
	
	public function setOptions(array $options = array()){
		if($this->method == Method::POST){
			curl_setopt($this->ch,CURLOPT_URL, $this->url);
			curl_setopt($this->ch,CURLOPT_POST, $this->method);
			curl_setopt($this->ch,CURLOPT_POSTFIELDS, $options); 
		}else{
			$qrtStr = '?';
			$isFirst = true;
			foreach($options as $key => $value){
				$qrtStr .= ($isFirst?'':'&').$key.'='.urlencode($value);
				$isFirst = false;
			}
			curl_setopt($this->ch, CURLOPT_URL, $this->url . $qrtStr); 
		}
	}
	
	public function setHeaders(array $header = array()){
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
	}
	

	public function getResponse(){
		if($this->response){
			return $this->response;
		}
		$response = curl_exec($this->ch);
		$error    = curl_error($this->ch);
		$errno    = curl_errno($this->ch);

		$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$this->header = substr($response, 0, $header_size);
		$this->body = substr($response, $header_size);
		
		if (0 !== $errno) {
			die(sprintf('Http error %s with code %d', $error, $errno));
		}

		return $this->response = $response;
	}
	
	public function close(){
		if (is_resource($this->ch)) {
			curl_close($this->ch);
		}
	}

	public function __toString(){
		return $this->getResponse();
	}
	
	public function getHeader(){
		$headers = array();
		foreach(explode("\n", $this->header) as $header){
			$headers[trim(substr($header,0,strpos($header,":")))] = trim(substr($header,strpos($header,":")+1,strlen($header)));
		}
		return $headers;
	}
	
	public function getBody(){
		return $this->body;
	}
}
class TestLevel3 {
	
	private $baseUrl = 'http://www.proveyourworth.net/level3/';
				
	public function run(){	
		$options = array();		
		$curl;
		$doc;
		$url;
		$headers;
		
		libxml_use_internal_errors(TRUE); 		
		$doc = new DOMDocument();
		$curl = new cURLRequest($this->baseUrl, Method::GET,true);
		echo $curl;
		$doc->loadHTML($curl->getResponse());
		$inputs = $doc->getElementsByTagName("input");
		foreach ($inputs as $input) {
			if($input->getAttribute('name') == 'statefulhash'){
				$options['statefulhash'] = $input->getAttribute("value");
			}
		}
		$options['username'] =  "' + ' and 1=1  --'";
		$curl->setConfig($this->baseUrl.'activate', Method::GET, $options);
		echo $curl;
		$headers = $curl->getHeader();
		$url = $headers['X-Payload-URL'];
		$curl->setConfig($headers['X-Payload-URL'], Method::GET);
		$curl->getResponse();
		$headers = $curl->getHeader();
		$body = $curl->getBody();
		$imgFile = substr($headers["Content-Disposition"],strpos($headers["Content-Disposition"],'=')+1,strlen($headers["Content-Disposition"]));
		if(file_exists($imgFile)){
			unlink($imgFile);
		}
		$fp = fopen($imgFile,'x');
		fwrite($fp, $body);
		fclose($fp);  
		$stamp = imagecreatefrompng('sign.png');
		$im = imagecreatefromjpeg($imgFile);
		$marge_right = 10;
		$marge_bottom = 10;
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
		imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
		if(file_exists($imgFile)){
			unlink($imgFile);
		}
		imagejpeg ( $im , $imgFile); 
		imagedestroy($im);
		$options = array('image'=> new CurlFile(realpath($imgFile), $headers['Content-Type']), 
						'code'=> new CurlFile(realpath('prove_your_worth_lvl_3.php.txt'), 'text/plain'),
						'resume'=> new CurlFile(realpath('curriculum-Dante-Fana-Badia.pdf'), 'application/pdf'),
						'email'=> "dfana@dfb.com.do", 'name'=> "Dante Faña Badia", 'aboutme' => "I'm sincere guy who like to work in my career, exciting learn and teach and improve myself every day. I'm good in this because this is something that I love and I'm enough persistent to learn.");
		$curl->setConfig($headers['X-Post-Back-To'], Method::POST, $options);
		echo $curl;	
		$curl->close();
	}
}

$page = new TestLevel3();
$page->run();

?>