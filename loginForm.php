<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password', 'required'),
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Запомнить меня',
            'username' => 'Email',
            'password' => 'Пароль'
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			if(!$this->_identity->authenticate())
				$this->addError('password','Не верный пароль или логин');
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		}
		else
			return false;
	}
        public function getUser($login){
            $user = Client::model()->find("email = :email", array(':email' => $login));
            if(isset($user)){
                return $user;
            }
            $user = Freelancer::model()->find("email = :email", array(':email' => $login));
            if(isset($user)){
                return $user;
            }
            return NULL;
        }
        
        public static function isClient($login){
            $user = Client::model()->find("email = :email", array(':email' => $login));
            if(isset($user)){
                return true;
            }else{
                return false;
            }
            
        }
		public static function isFreelancer($login){
            $user = Freelancer::model()->find("email = :email", array(':email' => $login));
            if(isset($user)){
                return true;
            }else{
                return false;
            }
            
        }
        public static function checkAuthId($id){
            $user = Client::model()->find("email = :email", array(':email' => Yii::app()->user->name));
            if(!isset($user->id)){
                $user = Freelancer::model()->find("email = :email", array(':email' => Yii::app()->user->name));
            }
            if($user->id == $id){
                return true;
            }else{
                return false;
            }
        }
        public static function getPhoto($width='auto', $height='auto'){
            $user = Freelancer::model()->find("email = :email", array(':email' => Yii::app()->user->name));
            if(isset($user->photo)){
                return CHtml::image('/images/'.$user->photo, array('width' => $width, 'height' => $height));
            }
            
        }
        public static function isPro() {
            $user = Client::model()->find("email = :email", array(':email' => Yii::app()->user->name));
            if(!isset($user->id)){
                $user = Freelancer::model()->find("email = :email", array(':email' => Yii::app()->user->name));
            }
            if($user->is_pro == 1){
                return true;
            }else{
                return false;
            }
        }
}
