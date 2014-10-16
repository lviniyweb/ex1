<?

class PaymentController extends Controller {
	
	
    /*
        Всё начинается здесь. Заводим в базе запись с новым выставленным счетом, 
        и передаем компоненту его ID, сумму, краткое описание и опционально 
        e-mail пользователя. Можно не выносить эти данные в отдельную модель, 
        а использовать атрибуты оформленного пользователем заказа 
        (для интернет-магазинов).
    */
	/*
    public function actionIndex() {
        // Выставляем счет
        $invoice = new Invoice;
        if (isset($_POST['Invoice'])) {
            $invoice->attributes = $_POST['Invoice'];
            $invoice->user_id = Yii::app()->user->id;
            $invoice->description = 'Внесение средств на личный счет.';
            if ($invoice->save()) {
                // Компонент переадресует пользователя в свой интерфейс оплаты
                Yii::app()->robokassa->pay(
                    $invoice->amount,
                    $invoice->id,
                    $invoice->description,
                    Yii::app()->user->profile->email
                );
            }
        }
    }
	*/
    /*
        К этому методу обращается робокасса после завершения интерактива 
        с пользователем. Это может произойти мгновенно либо в течение нескольких 
        минут. Здесь следует отметить счет как оплаченный либо обработать 
        отказ от оплаты.
    */
	/*
    public function actionResult() {
        $rc = Yii::app()->robokassa;

        // Коллбэк для события "оплата произведена"
        $rc->onSuccess = function($event){
            $transaction = Yii::app()->db->beginTransaction();
            // Отмечаем время оплаты счета
            $InvId = Yii::app()->request->getParam('InvId');
            $invoice = Invoice::model()->findByPk($InvId);
            $invoice->paid_at = new CDbExpression('NOW()');
            if (!$invoice->save()) {
                $transaction->rollback();
                throw new CException("Unable to mark Invoice #$InvId as paid.\n" 
                    . CJSON::encode($invoice->getErrors()));
            }
            $transaction->commit();
        };

        // Коллбэк для события "отказ от оплаты"
        $rc->onFail = function($event){
            // Например, удаляем счет из базы
            $InvId = Yii::app()->request->getParam('InvId');
            Invoice::model()->findByPk($InvId)->delete();
        };

        // Обработка ответа робокассы
        $rc->result();
    }
	*/
    /*
        Сюда из робокассы редиректится пользователь 
        в случае отказа от оплаты счета.
    */
	/*
    public function actionFailure() {
        Yii::app()->user->setFlash('global', 'Отказ от оплаты. Если вы столкнулись 
            с трудностями при внесении средств на счет, свяжитесь 
            с нашей технической поддержкой.');

        $this->redirect(array('index'));
    }
	*/
    /*
        Сюда из робокассы редиректится пользователь в случае успешного проведения 
        платежа. Обратите внимание, что на этот момент робокасса возможно еще 
        не обратилась к методу actionResult() и нам неизвестно, поступили средства 
        на счет или нет.
    */
	/*
    public function actionSuccess() {
        $InvId = Yii::app()->request->getParam('InvId');
        $invoice = Invoice::model()->findByPk($InvId);
        if ($invoice) {
            if ($invoice->paid_at) {
                // Если робокасса уже сообщила ранее, что платеж успешно принят
                Yii::app()->user->setFlash('global', 
                    'Средства зачислены на ваш личный счет. Спасибо.');
            } else {
                // Если робокасса еще не отзвонилась
                Yii::app()->user->setFlash('global', 'Ваш платеж принят. Средства 
                    будут зачислены на ваш личный счет в течение нескольких минут. 
                    Спасибо.');
            }
        }

        $this->redirect(array('index'));
    }
	*/
	// данные о платеже
	
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
