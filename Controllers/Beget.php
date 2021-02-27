<?php
use App\Config;


class Beget{

	public $apiurl = 'https://api.beget.com/api';
	public $config;

	public function __construct(){
		$this->config = new Config();
	}

	/* Выбрать все домены из системы Бегет */
	public function getDomians(){
		$filename = $this->apiurl.'/domain/getList?login='.$this->config->login.'&passwd='.$this->config->passwd.'&output_format=json';
		$domains = file_get_contents($filename);
		$domains = json_decode($domains,true);
		return $domains;
	}

	/* Добавить DNS записи в системе Бегет */
	public function edetDnsDomain($domain){
		$in_data = '{"fqdn":"'.$domain.'","records":{"DNS":[{"priority":10, "value":"'.$this->config->ns1.'"},{"priority":20,"value":"'.$this->config->ns2.'"}]}}';
		$in_data = urlencode($in_data);
		$result = file_get_contents($this->apiurl.'/dns/changeRecords?login='.$this->config->login.'&passwd='.$this->config->passwd.'&input_format=json&output_format=json&input_data='.$in_data);
		$result = json_decode($result, true);
		$add_dns = $result["answer"]["result"];
		return $add_dns;
	}

	/* Добавить Суб Домен в Бегет */
	public function addSubDomain($sub,$domain_id){
		$in_data = '{"subdomain":"'.$sub.'","domain_id":'.$domain_id.'}';
		$in_data = urlencode($in_data);
		$result = file_get_contents($this->apiurl.'/domain/addSubdomainVirtual?login='.$this->config->login.'&passwd='.$this->config->passwd.'&input_format=json&output_format=json&input_data='.$in_data);
		$result = json_decode($result, true);
		return $result;
	}

	/* Добавить Каталог Сайта в Бегет */
	public function addSiteCatalog($sd){
		$in_data = '{"name":"'.$sd.'"}';
		$in_data = urlencode($in_data);
		$result = file_get_contents($this->apiurl.'/site/add?login='.$this->config->login.'&passwd='.$this->config->passwd.'&input_format=json&output_format=json&input_data='.$in_data);
		$result = json_decode($result, true);
		return $result;
	}

	/* Прилинковать Домен к Сайту в Бегет */
	public function addCatalogLinkInDomain($sub_domian_id,$site_id){
		$in_data = '{"domain_id":'.$sub_domian_id.',"site_id":'.$site_id.'}';
		$in_data = urlencode($in_data);
		$result = file_get_contents($this->apiurl.'/site/linkDomain?login='.$this->config->login.'&passwd='.$this->config->passwd.'&input_format=json&output_format=json&input_data='.$in_data);
		$result = json_decode($result, true);
		return $result;
	}

	/* Функция поиска элементов в многомерном масиве, ключи $key которых равняются $value*/
	public function search($array, $key, $value)
	{
		$results = array();
		if (is_array($array))
		{
			if (isset($array[$key]) && $array[$key] == $value)
				$results[] = $array;

			foreach ($array as $subarray)
				$results = array_merge($results, $this->search($subarray, $key, $value));
		}
		return $results;
	}
	
}
?>