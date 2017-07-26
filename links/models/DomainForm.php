<?php

namespace dx\links\models;

use Yii;
use yii\base\Model;

class DomainForm extends Model
{

	/**
	 * @var string
	 */
	public $scheme;

	/**
	 * @var string
	 */
	public $host;

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'scheme' => 'Протокол',
			'host' => 'Хост',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['scheme', 'string', 'max'=> 10],
			['host', 'string', 'max'=> 100],
			[['scheme', 'host'], 'required'],
		];
	}

	/**
	 * Save object
	 * @return boolean
	 */
	public function save()
	{
		$object = Domain::find()->where(['scheme' => $this->scheme, 'host' => $this->host])->one();
		if ($object === null) {
			$object = new Domain([
				'scheme' => $this->scheme,
				'host' => $this->host,
			]);
			if (!$object->save(false))
				return false;

			$link = new Url(['url' => '/']);
			$object->link('urls', $link);
		}

		$user = Yii::$app->getUser();
		$user_id = $user->getId();
		$link = $object->getUsers()->where(['user_id' => $user_id])->one();
		if ($link === null) {
			$link = new DomainUser(['user_id' => $user_id, 'hash' => md5($user_id . ':' . $object->getDomain())]);
			if ($user->hasProperty('isAdmin') && $user->isAdmin)
				$link->verified = true;
			
			$object->link('users', $link);
		}

		return true;
	}

}
