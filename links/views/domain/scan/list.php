<?php

use yii\grid\GridView;
use yii\helpers\Html;

$summary = Yii::t('yii', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', ['count' => $total]);

?>
<?= GridView::widget([
	'options' => ['class' => 'scan-list'],
	'summary' => Html::tag('div', $summary, ['class' => 'scan-summary']),
	'dataProvider' => $dataProvider,
	'tableOptions' => ['class' => 'table table-bordered'],
	'rowOptions' => function($model, $key, $index, $grid) {
		$options = [];

		if ($model->status !== null) {
			switch ($model->status) {
				case 200:
					Html::addCssClass($options, 'success');
					break;
				case 301:
				case 302:
					Html::addCssClass($options, 'warning');
					break;
				default:
					Html::addCssClass($options, 'danger');
					break;
			}
		}

		return $options;
	},
	'columns' => [
		[
			'header' => 'Статус',
			'attribute' => 'status',
			'options' => ['style' => 'width:100px'],
		],
		[
			'header' => 'Адрес',
			'format' => 'raw',
			'attribute' => 'url',
			'content' => function($model, $key, $index, $column) {
				//url address
				$url = Html::encode($model->url);

				$addition = '';

				//seo
				if ($model->status == 200) {
					$title = Html::tag('div', '<b>Title:</b> ' . Html::encode($model->title));
					$description = Html::tag('div', '<b>Description:</b> ' . Html::encode($model->description));
					$keywords = Html::tag('div', '<b>Keywords:</b> ' . Html::encode($model->keywords));
					$addition = Html::tag('small', $title . $description . $keywords, ['class' => 'scan-addition-block hidden']);
				}

				//redirect
				if ($model->status == 301 || $model->status == 302) {
					$redirect = Html::tag('div', '<b>Redirect to:</b> ' . Html::encode($model->redirect));
					$addition = Html::tag('small', $redirect, ['class' => 'scan-addition-block hidden']);
				}

				//icon
				$icon = '';
				if (!empty($addition))
					$icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-info-sign scan-addition']) . '&nbsp;';

				//caption
				$caption = Html::tag('div', Html::tag('div', $icon . $url), ['class' => 'scan-caption-block']);

				return $caption . $addition;
			},
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'options' => ['style' => 'width:25px'],
			'template' => '{scan}',
			'buttons' => [
				'scan' => function($url, $model, $key) {
					return Html::a('<span class="glyphicon glyphicon-repeat"></span>', [
						'scan-url',
						'id' => $model->id,
					], [
						'class' => 'scan-url',
						'title' => 'Сканировать',
						'aria-label' => 'Сканировать',
						'data-pjax' => 0,
					]);
				},
			],
		],
	],
]) ?>
