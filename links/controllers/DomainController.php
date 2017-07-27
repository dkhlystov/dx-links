<?php

namespace dx\links\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use dx\links\models\Domain;
use dx\links\models\DomainForm;
use dx\links\models\DomainSearch;
use dx\links\models\Url;
use dx\links\utils\UrlParser;

class DomainController extends Controller
{

	/**
	 * List
	 * @return string
	 */
	public function actionIndex()
	{
		$model = new DomainForm;

		if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
			Yii::$app->getSession()->setFlash('success', 'Объект успешно добавлен.');

			return $this->refresh();
		}

		return $this->render('index', [
			'search' => new DomainSearch,
			'model' => $model,
		]);
	}

	/**
	 * Delete
	 * @param integer $id 
	 * @return string
	 */
	public function actionDelete($id)
	{
		$object = Domain::findOne($id);
		if ($object === null)
			throw new BadRequestHttpException('Объект не найден.');

		$object->unlink('users', $object->user, true);
		if ($object->getUsers()->count() == 0 && $object->getUrls()->count() == 0)
			$object->delete();

		Yii::$app->getSession()->setFlash('success', 'Объект успешно удален.');

		return $this->redirect(['index']);
	}

	/**
	 * Scan
	 * @param integer $id 
	 * @return string
	 */
	public function actionScan($id)
	{
		$object = Domain::findOne($id);
		if ($object === null)
			throw new BadRequestHttpException('Объект не найден.');

		$dataProvider = new ActiveDataProvider([
			'query' => $object->getUrls(),
			'key' => 'id',
			// 'pagination' => false,
		]);

		return $this->render('scan', ['domain' => $object, 'dataProvider' => $dataProvider]);
	}

	/**
	 * Scan next url for domain
	 * @param integer|null $domain_id domain id
	 * @param integer|null $id url id
	 * @return string
	 */
	public function actionScanUrl($domain_id = null, $id = null)
	{
		//domain and url
		if ($id !== null) {
			$url = Url::findOne($id);
			if ($url === null)
				throw new BadRequestHttpException('Объект не найден.');

			$object = $url->domain;
		} else {
			$object = Domain::findOne($domain_id);
			if ($object === null)
				throw new BadRequestHttpException('Объект не найден.');
			
			$url = $object->getUrls()->andWhere(['status' => null])->one();
		}

		//updated items
		$items = [];
		if ($url !== null) {
			$items[$url->id] = $url;
			foreach (UrlParser::scan($url) as $item)
				$items[$item->id] = $item;
		}

		//make data provider
		$dataProvider = new ArrayDataProvider([
			'allModels' => $items,
			'pagination' => false,
		]);

		//total
		$total = $object->getUrls()->count();

		return Json::encode([
			'ready' => (integer) $object->getUrls()->where(['not', ['status' => null]])->count(),
			'total' => (integer) $total,
			'view' => $this->renderPartial('scan/list', ['dataProvider' => $dataProvider, 'total' => $total]),
		]);
	}

	public function actionConformity($src_id, $dest_id, $unfinished = true)
	{
		$src = Domain::findOne($src_id);
		if ($src === null)
			throw new BadRequestHttpException('Объект не найден.');

		$dest = Domain::findOne($dest_id);
		if ($dest === null)
			throw new BadRequestHttpException('Объект не найден.');

		$dataProvider = new ActiveDataProvider([
			'query' => $src->getUrls(),
			// 'pagination' => false,
		]);

		return $this->render('conformity', ['src' => $src, 'dest' => $dest, 'unfinished' => $unfinished, 'dataProvider' => $dataProvider]);
	}

	public function actionConformitySet($src_id, $dest_id)
	{
		$src = Url::findOne($src_id);
		if ($src === null)
			throw new BadRequestHttpException('Объект не найден.');

		$dest = Url::findOne($dest_id);
		if ($dest === null)
			throw new BadRequestHttpException('Объект не найден.');

		$old = $src->getConfirmity($dest->domain_id)->one();
		if ($old !== null)
			$src->unlink('confirmity', $old, true);

		$src->link('confirmity', $dest, ['domain_id' => $dest->domain_id]);

		return Json::encode([
			'success' => true,
			'html' => $this->renderPartial('conformity/item', ['model' => $dest]),
		]);
	}

}
