<?php

namespace dx\links\models;

use yii\db\ActiveRecord;

class DomainUser extends ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'link_domain_user';
	}

	/**
	 * Domain relation
	 * @return ActiveQueryInterface
	 */
	public function getDomain()
	{
		return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
	}

}
