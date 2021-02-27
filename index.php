<style>
input,select{padding:5px; margin-right:10px; margin-bottom:10px;}
</style>
<?
include('App/config.php');

/* Подключает все имеющиеся файлы в папках $array_paths[] BEGIN */
function __autoload($class_name)
{
    $array_paths = array(
        '/Models/',
        '/Сomponents/',
        '/Controllers/',
    );
	
    foreach ($array_paths as $path) {
        $path = __DIR__ . $path . $class_name . '.php';
        if (is_file($path)) {
            include_once $path;
        }
    }
}
/* END */

$beget   = new Beget();
$cloud   = new Cloud();
$kloak   = new Kloak();
$keitaro = new Keitaro();

	function addKeitaro($domain,$landing){
		global $keitaro;
		
		echo 'KEITARO: ';
		$campaign = $keitaro->addCampaign($domain);
			
		if($campaign["id"] == true){
			echo 'Campaign Added! ';

			$streams = $keitaro->addStreams($campaign,$landing);
			foreach($streams as $stream){
				if($stream["error"]){
					echo 'ERR:'; var_dump($stream);
				}else{
					echo $stream["name"].' Added! ';
				}
			}
				
			$domain = $keitaro->addSubDomain($domain,$campaign);
			if($domain[0]["default_campaign_id"] == true){
				echo 'Create Domain and Added in Campaign! OK!';
			}else{
				echo 'ERR: '; var_dump($domain);
			}

		}else{
				echo 'ERR: '; var_dump($campaign);
		}
	}

	include('form.php');

if($_GET['domain']){
	
	$_domain =  $_GET['domain'];


	$item_domain = $beget->search($domains, 'fqdn', $_domain);

	/* Добавление DNS CLOUD к домену в системе BEGET */
	if($_GET['dns'] == true){
		$add_dns = $beget->edetDnsDomain($_domain);
		echo $_domain.' DNS: '.$add_dns.'<br>';
	}
	/* END */

	/* Добавление Домена в систему CLOUD */
	if($_GET['ADD_DCFKKT'] == true){
		$cloud->addDomain($_domain);
		$kloak->addDomain($_domain,$_GET['black'],$_GET['white'],$_GET['geo'],$_GET['type']);
		addKeitaro($_domain,$_GET['landing']);
		echo '<hr>';
	}
	/* END */

	$pfx = mb_strtolower($_GET['geo']);
	$pfx = $pfx.'-';
	$arr_en = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
	//echo count($arr_en);

	$count_sd = $_GET['count'];
	$i=0;
	$n=0;
	$mn = 0;

	while ($i < $count_sd) {

		unset($sub_domian_id);
		unset($site_id);
		unset($complected);

		/* Генерация Префиксов домена BEGIN */
		$sub = $pfx.$arr_en[$n].$m;

		$i++;
		$n++;
		if($n == count($arr_en)){
			$mn++; 
			$m = $arr_en[$mn];
			$n = 0;
		}
		/* END */

		$sd = $sub.'.'.$_domain;
		
		echo $sd.'<br>';
		echo 'BEGET: ';
		/* Метод добавляет Суб Домен в Бегет BEGIN */
		$result = $beget->addSubDomain($sub,$item_domain[0]["id"]);		
		$sub_domian_id = $result["answer"]["result"];
		/* END */

		/* Метод добавляет каталог Сайта в Бегет BEGIN */
		if($sub_domian_id){
			echo 'Domain Added; ';
			$result = $beget->addSiteCatalog($sd);
			$site_id = $result["answer"]["result"];
		}else{
			echo $result["answer"]["errors"][0]["error_text"];
			exit;
		}
			$count_sd++;
		/* END */
		
		//* Метод прилинковывает домен к сайту в Бегет BEGIN *
		if($site_id){
			echo 'Catalog Site Added; ';
			$result = $beget->addCatalogLinkInDomain($sub_domian_id,$site_id);
			$complected = $result["answer"]["result"];
		}else{
			echo $result["answer"]["errors"][0]["error_text"];
		}
		/* END */
		
		
		if($complected){ /* if($sub_domian_id) */
			echo 'Links Added; Complected !!! <br>';
			$cloud->addSubDomain($sub,$_domain);
			$kloak->addDomain($sd,$_GET['black'],$_GET['white'],$_GET['geo'],$_GET['type']);

			addKeitaro($sd,$_GET['landing']);
		}else{
			echo $result["answer"]["errors"][0]["error_text"];
			echo '@ Failing ***';
		}
		/* END */

	echo '<hr>';
	}
}

?>