<?php
use App\Config;


class Cloud{

	public $config, $auth, $account, $zone_id;
	public $apiurl = $this->apiurl.'';

	public function __construct()
    {
		$this->config = new Config();
		$this->auth = '-H "X-Auth-Email: '.$this->config->email.'" -H "X-Auth-Key: '.$this->config->api_key.'" -H "Content-Type: application/json"';
		$this->accountData();
		$this->zone_id = 0;
    }
	
	
	/* User's Account Memberships */
	public function accountData(){
		$url = $this->apiurl.'/memberships?status=accepted&page=1&per_page=1&order=status&direction=desc'; // List Memberships //
		$cmd='curl -X GET "'.$url.'" '.$this->auth.'';
		$result = exec($cmd,$result);
		$this->account = json_decode($result,true);
	}
	

	public function addDomain($domain){
		echo 'CLOUD: ';
		/* Create Zone BEGIN */
		if($this->account["success"] == true){
			echo 'Users Account Memberships! ';
			$account_id = $this->account["result"][0]["account"]["id"]; // Account of which the zone is created in //
			$url = $this->apiurl.'/zones';
			$data = ' --data "{\"name\":\"'.$domain.'\",\"account\":{\"id\":\"'.$account_id.'\"},\"jump_start\":true,\"type\":\"full\"}"';
			$cmd = 'curl -X POST "'.$url.'" '.$this->auth.' '.$data;
			$result = exec($cmd,$result);
			$zone = json_decode($result,true);
			echo $zone["errors"][0]["message"];
		}else{ echo '; Users Account Memberships Error!';  exit; }
		/* END */

		/* IPv6 Compatibility OFF */
		if($zone["success"] == true){
			echo 'Zone Created! ';
			$this->zone_id = $zone["result"]["id"];
			$url = $this->apiurl.'/zones/'.$this->zone_id.'/settings/ipv6';
			$data = ' --data "{\"value\":\"off\"}"';
			$cmd = 'curl -X PATCH "'.$url.'" '.$this->auth.' '.$data;
			$result = exec($cmd,$result);
			$ipv6 = json_decode($result,true);
			//var_dump($ipv6);
		}else{ echo '; Create Zone Error!'; exit; }
		/* END */
		
		if($ipv6["success"] == true){
			echo 'IPv6 Compatibility OFF! ';
			$this->addDns($this->zone_id,$domain);
		}else{ echo '; IPv6 Compatibility Error!'; exit; }

		echo 'COMPLECTED !!! ';
	}
	
	
	public function addDns($zone_id,$domain){
		/* Create DNS Record WWW zone A BEGIN */
		$url = $this->apiurl.'/zones/'.$zone_id.'/dns_records';
		$data = ' --data "{\"type\":\"A\",\"name\":\"WWW\",\"content\":\"'.$this->config->dns_ip.'\",\"ttl\":1,\"priority\":10,\"proxied\":true}"';
		$cmd = 'curl -X POST "'.$url.'" '.$this->auth.' '.$data;
		$result = exec($cmd,$result);
		$dns = json_decode($result,true);
		/* END */
		
		/* Create DNS Record DOMAIN zone A BEGIN */
		if($dns["success"] == true){
			$url = $this->apiurl.'/zones/'.$zone_id.'/dns_records';
			$data = ' --data "{\"type\":\"A\",\"name\":\"'.$domain.'\",\"content\":\"'.$this->config->dns_ip.'\",\"ttl\":1,\"priority\":10,\"proxied\":true}"';
			$cmd = 'curl -X POST "'.$url.'" '.$this->auth.' '.$data;
			$result = exec($cmd,$result);
			$dns = json_decode($result,true);
		}
		/* END */	
		
		if($dns["success"] == true){
			echo ' "WWW" Create DNS Record!';
		}else{	echo '; "WWW" Create DNS Record Error!'; exit; }
	}


	public function addSubDomain($sub,$domain){
		echo 'CLOUD: ';
		/* Domain ZoneID BEGIN */
		if($this->zone_id == 0){
			if($this->account["success"] == true){
				$account_id = $this->account["result"][0]["account"]["id"];
				
				$url = $this->apiurl.'/zones?name='.$domain.'&status=pending,active&account.id='.$account_id.'&page=1&per_page=1&order=status&direction=desc&match=all';
				$cmd='curl -X GET "'.$url.'" '.$this->auth.'';
				$result = exec($cmd,$result);

				$zone = json_decode($result,true);
				$this->zone_id = $zone["result"][0]["id"];
			}else{ echo '; Domain ZoneID Users Account Memberships Error!';  exit;}
		}
		/* END */

		/* Create DNS Record SubDomain zone A BEGIN */
			$sd =  $sub.'.'.$domain;
			$url = $this->apiurl.'/zones/'.$this->zone_id.'/dns_records';
			$data = ' --data "{\"type\":\"A\",\"name\":\"'.$sd.'\",\"content\":\"'.$this->config->dns_ip.'\",\"ttl\":1,\"priority\":10,\"proxied\":true}"';
			$cmd = 'curl -X POST "'.$url.'" '.$this->auth.' '.$data;
			$result = exec($cmd,$result);
			$dns = json_decode($result,true);
			
			if($dns["success"] == true){
				echo ' "'.$sub.'" Create DNS Record! <br>';
			}else{	echo '; Create DNS Record Error!'; exit; }
		/* END */
	}

}
?>


