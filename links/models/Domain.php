<?php

namespace dx\links\models;

use Yii;
use yii\db\ActiveRecord;

class Domain extends ActiveRecord
{

	//protocols
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	/**
	 * @var DomainUser
	 */
	private $_user;

	/**
	 * @var string[]
	 */
	private static $schemeNames = [
		self::SCHEME_HTTP => self::SCHEME_HTTP . '://',
		self::SCHEME_HTTPS => self::SCHEME_HTTPS . '://',
	];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'link_domain';
	}

	/**
	 * Protocol names getter
	 * @return string[]
	 */
	public static function getSchemeNames()
	{
		return self::$schemeNames;
	}

	/**
	 * Domain getter
	 * @return string
	 */
	public function getDomain()
	{
		return $this->scheme . '://' . $this->host;
	}

	/**
	 * Urls relation
	 * @return ActiveQueryInterface
	 */
	public function getUrls()
	{
		return $this->hasMany(Url::className(), ['domain_id' => 'id'])->inverseof('domain');
	}

	/**
	 * Users relation
	 * @return ActiveQueryInterface
	 */
	public function getUsers()
	{
		return $this->hasMany(DomainUser::className(), ['domain_id' => 'id']);
	}

	public function getUser($id = null)
	{
		if ($this->_user !== null)
			return $this->_user;

		if ($id === null)
			$id = Yii::$app->getUser()->getId();

		return $this->_user = $this->getUsers()->where(['user_id' => $id])->one();
	}

}
