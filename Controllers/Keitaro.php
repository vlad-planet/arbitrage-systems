<?php			// ВСЁ ХОРОШО!!!
use App\Config;

				
class Keitaro{

	public $config, $domain;

	public function __construct(){
		$this->config = new Config();
	}


	/* Запросить список Лендингов BEGIN */
	public function landingPages(){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->admin_api.'/landing_pages');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Key: '.$this->config->apiKey.''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$result = json_decode($result,true);
		return $result;
	}
	/* END */
	

	/* Создание Компании BEGIN */
	public function addCampaign($domain){
		
		$alias =  str_replace(".", "-", $domain);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,  $this->config->admin_api.'/campaigns');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Key: '.$this->config->apiKey.'')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		$params = [
			'name' =>  'ARB-G SYSTEM: '.$domain,
			'alias' => $alias,
			'group_id' => 5,
		];
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
		$result = curl_exec($ch);
		$result = json_decode($result,true);
		return $result;
	}
	/* END */
	
	
	/* Создание Потоков BEGIN */		
	public function addStreams($campaign,$landing){	
	
		$flow = array();

		if($landing[1] == true){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,  $this->config->admin_api.'/streams');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Key: '.$this->config->apiKey.''));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			$params = [
				'campaign_id' =>  $campaign["id"],
				'type' =>  'regular',
				'name' =>  'Black Filter',
				'position' =>  1,
				'action_type' =>  'http',
				'schema' =>  'landings',
				'filters' => [['name' => 'imklo_detect', 'mode' => 'black']],
				'landings' => [['state' => 'active', 'share' => 100, 'landing_id' => $_GET['landing'][1]]]
			];
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
			$result = curl_exec($ch);
			$flow[] = json_decode($result,true);
		}

		if($landing[2] == true){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,  $this->config->admin_api.'/streams');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Key: '.$this->config->apiKey.''));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			$params = [
				'campaign_id' =>  $campaign["id"],
				'type' =>  'regular',
				'name' =>  'White Filter',
				'position' =>  2,
				'action_type' =>  'http',
				'schema' =>  'landings',
				'filters' => [['name' => 'imklo_detect', 'mode' => 'white']],
				'landings' => [['state' => 'active', 'share' => 100, 'landing_id' => $_GET['landing'][2]]]
			];
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
			$result = curl_exec($ch);
			$flow[] = json_decode($result,true);
		}
		return $flow;
	}
	/* END */


	/* Создание Домена BEGIN */
	public function addSubDomain($domain,$campaign){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->admin_api.'/domains');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Api-Key: '.$this->config->apiKey.''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		$params = [
			'name' => $domain,
			'is_ssl' => 'http',
			'wildcard' => true,
			'default_campaign_id' => $campaign["id"]	
		];
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
		$result = curl_exec($ch);
		$result = json_decode($result,true);
		return $result;
	}
	/* END */

}
?>