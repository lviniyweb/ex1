

<?
include 'main.php';
//get places for sity list
	function getPlaces(){
		$data = file_get_contents(DOCUMENT_ROOT.'/data/city.dat');
		if(empty($data)){
			$gis_category = file_get_contents('http://catalog.api.2gis.ru/project/list?version=1.3&key=rulmkn8285');
			$gis_obj = json_decode($gis_category);
			file_put_contents(DOCUMENT_ROOT.'/data/city.dat', $gis_category);
		}else{
			$gis_obj = json_decode($data);
		}
		
		
		$city = array();
		foreach($gis_obj->result as $city_code){
			 $city[] = array(
			 'name' => $city_code->name,
			 'url' => str_replace('_', '-', $city_code->code)
			 );

		}
		return $city;
	}
	$city = getPlaces();
//get categories from API	
	function getNameCat($alias){
		$data = file_get_contents(DOCUMENT_ROOT.'/data/categories.dat');
		if(empty($data)){
			$gis_category = file_get_contents('http://catalog.api.2gis.ru/rubricator?where='.CITY.'&version=1.3&key=rulmkn8285&show_children=1');
			$gis_obj = json_decode($gis_category);
			file_put_contents(DOCUMENT_ROOT.'/data/categories.dat', $gis_category);
		}else{
			$gis_obj = json_decode($data);
		}
		foreach($gis_obj->result as $p){
			foreach($p->children as $c){
				if($c->alias == $alias){
					return $c->name;
				}
			}
		}
	}
	function getURIcategory($name){
		$data = file_get_contents(DOCUMENT_ROOT.'/data/categories.dat');
		if(empty($data)){
			$gis_category = file_get_contents('http://catalog.api.2gis.ru/rubricator?where='.CITY.'&version=1.3&key=rulmkn8285&show_children=1');
			$gis_obj = json_decode($gis_category);
			file_put_contents(DOCUMENT_ROOT.'/data/categories.dat', $gis_category);
		}else{
			$gis_obj = json_decode($data);
		}
		foreach($gis_obj->result as $p){
			foreach($p->children as $c){
				if($c->name == $name){
					return '/'.$p->alias.'/'.$c->alias;
				}
			}
		}
	}
	
	
	$russion = db_rows("SELECT * FROM domins WHERE is_show and is_ua = 0 ORDER BY sort");
	$ukraine = db_rows("SELECT * FROM domins WHERE is_show and is_ua = 1 ORDER BY sort");
	
	
	
	$gis = file_get_contents('http://catalog.api.2gis.ru/project/list?version=1.3&key=rulmkn8285');
	$gis_obj1 = json_decode($gis);	
	foreach($gis_obj1->result as $c){
		db_insert(' domins ', array('name' => $c->name, 'url' => $c->code, 'is_show' => 1));
	}
	
	
	
	
	$_TITLE = '';
	$_TITLE_AFT = 'Справочник компаний '.CITY_CATEGORY.' 2013 - 2014';
	$_DESC = '';
	$_KEYWORDS = '';
	if(!isset($uri_array[1])){
		$_TITLE = CITY;
		$_KEYWORDS = "Все компании в ".CITY_WHERE.", Каталог организаций в ".CITY_WHERE.", компании ".CITY_CATEGORY." 2013-2014, renlev.ru - справочник компаний ".CITY_CATEGORY;
		$_DESC = "Справочник компаний ".CITY_CATEGORY." 2014. Все направления деятельности компаний, фирм и организаций в ".CITY_WHERE;
		
		$data = file_get_contents(DOCUMENT_ROOT.'/data/categories.dat');
		if(empty($data)){
			$gis_category = file_get_contents('http://catalog.api.2gis.ru/rubricator?where='.CITY.'&version=1.3&key=rulmkn8285&show_children=1');
			$gis_obj = json_decode($gis_category);
			file_put_contents(DOCUMENT_ROOT.'/data/categories.dat', $gis_category);
		}else{
			$gis_obj = json_decode($data);
		}
		
	}elseif($uri_array[1] == 'profile' and isset($uri_array[2])){
		include DOCUMENT_ROOT.'/includes/profile.php';
	
	
	
	}elseif(isset($uri_array[2]) and $uri_array[1] != 'profile'){
		$what = getNameCat($uri_array[2]);
		
		$page = isset($_GET['page'])?$_GET['page']:1;
		
		$_TITLE = $what;
		if(isset($_GET['page'])){
				$_TITLE = $what. ', страница '.$_GET['page'];
		}
		$_DESC = "$what, ".CITY. "- справочник компаний 2013-2014 года.";
		$_KEYWORDS = "$what в ".CITY_WHERE.", $what 2014, лучшие $what, найти $what в ".CITY_WHERE.", цены на $what в ".CITY_WHERE.", отзывы о  $what в ".CITY_WHERE;
		@$gis_category = file_get_contents('http://catalog.api.2gis.ru/searchinrubric?where='.CITY.'&what='.$what.'&page='.$page.'&pagesize=20&version=1.3&key=rulmkn8285');
		@$gis_obj = json_decode($gis_category);
		
		
	   
		   $cnt = 20;
		   $total = $gis_obj->total;
		   
		   $pageCnt = ceil($total / $cnt);
	}elseif($uri_array[1] == 'owner'){
		$_TITLE = 'Владельцу компании';
	}
	
include 'templates/index.php';

?>
