<?php
use App\Config;


class Kloak{
	
	public $config, $url;
	
	public function __construct(){
		$this->config = new Config();
	}

	public function addDomain($domain,$black,$white,$geo,$type){
		
		echo 'KLOAK: ';
		
		$black = urlencode($black);
		$white = urlencode($white);

		if($curl = curl_init()) {
			$url = $this->config->url.'/nadd_domain';
			// устанавливаем параметры предстоящего запроса
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, "domain=".$domain."&black=".$black."&white=".$white."&geo=".$geo."&type=".$type."");
			// выполняем запрос
			$out = curl_exec($curl);
			// выводим результат
			$out = json_decode($out,true);
			if($out["succes"] == 'true'){
				echo 'Links Added! OK! <br>';
			}else{
				echo 'Links Added! Error!'; exit;
			}
			// закрываем curl
			curl_close($curl);
		}
	}

}
?>