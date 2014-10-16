<?php

/**
 * This is the model class for table "project".
 *
 * The followings are the available columns in table 'project':
 * @property string $id
 * @property string $name
 * @property string $desciption
 * @property string $date_create
 * @property string $price
 * @property string $range
 * @property string $currency
 * @property integer $is_show
 * @property string $status
 */
class Project extends CActiveRecord
{
	public $is_ord;
	public $catname;
    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'project';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, desciption, range', 'required'),
			array('is_show', 'numerical', 'integerOnly'=>true),
			array('name, price, range', 'length', 'max'=>255),
			array('currency', 'length', 'max'=>10),
			array('status', 'length', 'max'=>8),
			array('date_create, category_id', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, desciption, date_create, price, range, currency, is_show, status, category_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(


	
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название',
                        'category_id' => 'Категория',
			'desciption' => 'Описание',
			'date_create' => 'Дата создания ',
			'price' => 'Цена',
			'range' => 'Сроки',
			'currency' => 'Валюта',
			'is_show' => 'Is Show',
			'status' => 'Status',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('desciption',$this->desciption,true);
		$criteria->compare('date_create',$this->date_create,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('range',$this->range,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('is_show',$this->is_show);
		$criteria->compare('status',$this->status,true);
                $criteria->compare('category_id',$this->category_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    public function setRelationsWithClient($project_id){
        $username = Yii::app()->user->name;
        $cr = new CDbCriteria();
        $cr->compare('email', $username); 
        $client = Client::model()->find($cr);
        if(isset($client->id)){
            $sql = "insert into client2project (project_id, client_id) values (:project_id, :client_id)";
            $parameters = array(":project_id"=>$project_id, ':client_id' => $client->id);
            Yii::app()->db->createCommand($sql)->execute($parameters);
        }else{
            
        }
        
    }
    public static function fastUpdateModel($id ,$f, $v) {
        $sql = "UPDATE project  SET {$f} = :val WHERE id = :id ";
        $parameters = array(":val"=>$v, ':id' =>$id);
        if(Yii::app()->db->createCommand($sql)->execute($parameters)){
            return true;
        }
        
    }
    public static function answerProject($project_id = 0, $freelancer_id = 0, $text = ''){
        $sql = "INSERT INTO freelancer2project(freelancer_id, project_id,text) VALUES (:freelancer_id, :project_id, :text)";
        $parameters = array(":freelancer_id"=>$freelancer_id, ':project_id' => $project_id, ':text' => $text);
        if(Yii::app()->db->createCommand($sql)->execute($parameters)){
            return true;
        }
        return false;
    }
    
    public static function hasAnswer($id){
        if(!Yii::app()->user->isGuest){
            $name = Yii::app()->user->name;
            $user = Yii::app()->db->createCommand()
                ->select('f.id')
                ->from('freelancer2project fp')
                ->leftJoin('freelancer f', 'fp.freelancer_id = f.id')
                ->where('fp.project_id=:id and f.email = :email', array(':id'=>$id, ':email' => $name))
                ->queryRow();
            if(isset($user['id'])){
                return true;
            }else{
                return FALSE;
            }
    }else{
       return FALSE; 
    }
    } 

    public static function sendEmailToClient($email, $project_id){
        $url = CHtml::normalizeUrl(array('/project/'.$project_id));
        $text = <<<E
                <div>
                <h1><span style='background:rgb(0,174,40);color:#fff;padding:3px;'>G</span>Freelance.ru</h1>
                </div>
                Ответ на ваш проект <a href='$url'>здесь</a>($url)!
                
                Приятной Вам работы!
E;
        
        mail($email, 'Подписались под ваш проект на GFreelance.ru', $text);
    }
    
    public static function sendEmailToFreelancer($email, $project_id){
        $url = CHtml::normalizeUrl(array('/project/'.$project_id));
        $text = <<<E
                <div>
                <h1><span style='background:rgb(0,174,40);color:#fff;padding:3px;'>G</span>Freelance.ru</h1>
                </div>
                Сообщение от клиента <a href='$url'>здесь</a>($url)!
                
                Приятной Вам работы!
E;
        
        mail($email, 'Сообщение от клиента на GFreelance.ru', $text);
    }
    

    public static function getClient($id){
        $user = Yii::app()->db->createCommand()
        ->select('c.*, cat.name as catname')
        ->from('client2project c2p')
        ->leftJoin('client c', 'c2p.client_id = c.id')
        ->leftJoin('category cat', 'c.category_id = cat.id')
        ->where('c2p.project_id=:id', array(':id'=>$id))
        ->queryRow(); 
        return $user;   
    }
    public static function getAnswers($id){
        $answers = Yii::app()->db->createCommand()
        ->select('f.*, fp.text as answer, fp.date as date_create')
        ->from('freelancer2project fp')
        ->leftJoin('freelancer f', 'fp.freelancer_id = f.id')
        ->where('fp.project_id=:id', array(':id'=>$id))
        ->order('if(f.is_pro = 1,"f.is_pro, fp.date DESC","fp.date DESC")')
        ->query(); 
        return $answers;
    }
    
    
    

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Project the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}

