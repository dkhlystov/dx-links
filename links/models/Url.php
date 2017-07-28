<?php

namespace dx\links\models;

use yii\db\ActiveRecord;
use dx\links\utils\Socket;

class Url extends ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'link_url';
	}

	/**
	 * Domain relation
	 * @return ActiveQueryInterface
	 */
	public function getDomain()
	{
		return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
	}

	/**
	 * Sources relation
	 * @return ActiveQueryInterface
	 */
	public function getSrc()
	{
		return $this->hasMany(Url::className(), ['id' => 'src_id'])->viaTable('link_url_rel', ['dest_id' => 'id']);
	}

	/**
	 * Dest relation
	 * @return ActiveQueryInterface
	 */
	public function getDest()
	{
		return $this->hasMany(Url::className(), ['id' => 'dest_id'])->viaTable('link_url_rel', ['src_id' => 'id']);
	}

	/**
	 * Conformity relation
	 * @return ActiveQueryInterface
	 */
	public function getConformities()
	{
		return $this->hasMany(Url::className(), ['id' => 'dest_id'])->viaTable('link_url_confirmity', ['src_id' => 'id']);
	}

}
