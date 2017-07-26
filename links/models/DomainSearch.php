<?php

namespace dx\links\models;

use Yii;
use yii\data\ActiveDataProvider;

class DomainSearch extends Domain
{

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
		];
	}

	/**
	 * Search rules
	 * @return array
	 */
	public function rules() {
		return [
		];
	}

	/**
	 * Search function
	 * @param array|null $params Attributes array
	 * @return ActiveDataProvider
	 */
	public function getDataProvider($params = null) {
		if ($params === null)
			$params = Yii::$app->getRequest()->get();

		//ActiveQuery
		$query = static::find();
		$query->joinWith('users u', true, 'INNER JOIN')->andWhere(['u.user_id' => Yii::$app->user->id]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		//return data provider if no search
		if (!($this->load($params) && $this->validate()))
			return $dataProvider;

		//search
		$query->andFilterWhere(['like', 'code', $this->code]);

		return $dataProvider;
	}

}
