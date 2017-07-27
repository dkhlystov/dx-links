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
	 * @return ActiveRecordInterface
	 */
	public function getDomain()
	{
		return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
	}

	/**
	 * Sources relation
	 * @return ActiveRecordInterface
	 */
	public function getSrc()
	{
		return $this->hasMany(Url::className(), ['id' => 'src_id'])->viaTable('link_url_rel', ['dest_id' => 'id']);
	}

	/**
	 * Dest relation
	 * @return ActiveRecordInterface
	 */
	public function getDest()
	{
		return $this->hasMany(Url::className(), ['id' => 'dest_id'])->viaTable('link_url_rel', ['src_id' => 'id']);
	}

	public function getConfirmity($domain_id = null)
	{
		return $this->hasOne(Url::className(), ['id' => 'dest_id'])->viaTable('link_url_confirmity', ['src_id' => 'id'])->andWhere(['domain_id' => $domain_id]);
	}

}
