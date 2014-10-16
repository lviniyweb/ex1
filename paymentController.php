<?

class PaymentController extends Controller {
	
	
	public function actionInteraction() {
		$accountID = "53da3f4fbf4efca50d46eb33";
		//$key = "D1TahGC9A0QfvbqX"; //test
		$key = "HbE0kMTKApGKNNXy"; 
		
		$payment = new PaymentStatistics;
		
		if(!empty($_POST)){
			if($this->paymentValidate($_POST, $key, $accountID)){
				$payment->attributes = $_POST;
				$payment->save(false);
				if($_POST['ik_inv_st'] == "success"){
					//действия в случае успешного платежа
					$paymentID = explode("_",$_POST['ik_pm_no']);
					if(strpos($paymentID[0],"UC") === 0){

						$user_id = substr($paymentID[0],2);
						if($user_id && substr($paymentID[1],0,1) == "M"){
							if((substr($paymentID[1],1,2) == "1" && $_POST['ik_am'] == 10 ) || (substr($paymentID[1],1,2) == "3" && $_POST['ik_am'] == 20 ) || (substr($paymentID[1],1,2) == "6" && $_POST['ik_am'] == 40 )){
								$time = substr($paymentID[1],1) * 30;
							} else {
								unset($time);
							}
						} else{
							unset($time);
						}
						
							if(!empty($time) && !empty($user_id)){
								$user = Client::model()->findByPk($user_id);
								$user->is_pro = 1;
								$user->date_pro_end = date("Y-m-d",(time() + (3600 * 24 * $time)));
								$user->save(false);
							
							}
						
					}else if(strpos($paymentID[0],"UF") === 0){
						$user_id = substr($paymentID[0],2);
						if($user_id && substr($paymentID[1],0,1) == "M"){
							if((substr($paymentID[1],1,2) == "1" && $_POST['ik_am'] == 10 ) || (substr($paymentID[1],1,2) == "3" && $_POST['ik_am'] == 20 ) || (substr($paymentID[1],1,2) == "6" && $_POST['ik_am'] == 40 )){
								$time = substr($paymentID[1],1) * 30;
							} else {
								unset($time);
							}
						} else{
							unset($time);
						}
						
						if(!empty($time) && !empty($user_id)){
							$user = Freelancer::model()->findByPk($user_id);
							$user->is_pro = 1;
							$user->pro_date_end = date("Y-m-d",(time() + (3600 * 24 * $time)));
							$user->save(false);
						
						}
					}
				}else{
					//действия в случае неудачного платежа 
				}
				
			}
		}
		
		header('Status: 200 Ok'); 
	}
	 // callback успешного платежа
	public function actionSuccess() {
	  $this->render('success');
	}
	 // callback неудачного платежа 
	public function actionError() {
	  $this->render('error'); 
	 }
	// callback ожидания платежа
	public function actionWait() {
		$this->render('wait');
	}
	
	public function actionProPayment() {
		 if(!Yii::app()->user->isGuest){
            $user_em = Yii::app()->user->name;
			//var_dump(Yii::app()->user->name);
			$loginForm = new LoginForm;
			$user_id = $loginForm->getUser($user_em);
			$user_id = $user_id->id;
			if(LoginForm::isClient($user_em)){
				$user_type = "UC";
				
			}else if(LoginForm::isFreelancer($user_em)){
				$user_type = "UF";
				
			}else{
			
			}
        }
		$this->render('pro-payment',array('user_type'=>$user_type ,'user_id'=>$user_id)); 
	}
	
	private function paymentValidate($dataSet, $key, $id){
		$signOr = $dataSet['ik_sign'];
		unset($dataSet['ik_sign']); //удаляем из данных строку подписи
		ksort($dataSet,SORT_STRING); // сортируем по ключам в алфавитном порядке элементы массива
		array_push($dataSet, $key); // добавляем в конец массива "секретный ключ"
		$signString=implode(':', $dataSet); // конкатенируем значения через символ ":"
		$sign=base64_encode(md5($signString,true)); // берем MD5 хэш в бинарном виде по сформированной строке и кодируем в BASE 64
		if($sign == $signOr && $dataSet['ik_co_id'] == $id){
			return true;
		}else{
			return false;
		}
	} 
	
	private function priceValidate($dataSet, $price, $cur){
		//$dataSet['']
	}
}
