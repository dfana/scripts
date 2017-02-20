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
	
	public function __construct($url, $method, $referer, 
								$keepSessionAlive = false,
								array $options = array(), 
								array $header = array()){
		$this->response = false;
		$this->url = $url;
		$this->method = $method;
		$this->ch = curl_init();
		$this->setReferer($referer);
		$this->setOptions($options);
		$this->setHeaders($header);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		if($keepSessionAlive){
			$this->keepSession();
		}
	}
	
	public function setReferer($referer){
		curl_setopt($this->ch, CURLOPT_REFERER, $referer);
	}
	
	public function setConfig($url, $method, $referer, 
								array $options = array(), 
								array $header = array()){
		$this->response = false;
		$this->url = $url;
		$this->method = $method;
		$this->setReferer($referer);
		$this->setOptions($options);
		$this->setHeaders($header); 
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
				$qrtStr .= ($isFirst?'':'&').$key.'='.$value;
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
}


			
class TestLevel1 {
	
	private $baseUrl = 'http://www.proveyourworth.net/level1/';
				
	public function run(){	
		$options = array(
					'name' => 'Dante Faña Badia',
					'email' => 'dfana@dfb.com.do',
					'incoming_file' => new CurlFile(realpath('curriculum-Dante-Fana-Badia.pdf'), 'application/pdf'),
					'incoming_source' => new CurlFile(realpath('auto_submit.php.txt'), 'text/plain'),
					'session_hash' => '',
					'about' =>  "I'm sincere guy who like to work in my career, exciting learn and teach and improve myself every day. I'm good in this because this is something that I love and I'm enough persistent to learn.",
					'Submit'=> 'Submit'
				);		
				
		$curl = new cURLRequest($this->baseUrl, Method::GET,"http://google.com",true);
		echo $curl;
		$curl->setConfig($this->baseUrl, Method::GET, $this->baseUrl, 
						array('p'=>'begin', 'mistake'=>'very_little'));
		echo $curl;
		
		$doc = new DOMDocument();
		libxml_use_internal_errors(TRUE); 
		$doc->loadHTML($curl->getResponse());
		$inputs = $doc->getElementsByTagName("input");
		foreach ($inputs as $input) {
			if($input->getAttribute('name') == 'session_hash'){
				$options['session_hash'] = $input->getAttribute("value");
			}
		}
		
		$curl->setConfig($this->baseUrl.'?p=auto_submit&mistake=very_little', Method::POST, 
						$this->baseUrl.'?p=begin&mistake=very_little', $options);
		var_dump($options);
		echo $curl;
		$curl->close();
	}
}

$page = new TestLevel1();
$page->run();

?>