<?php

use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use dx\links\assets\DomainAsset;
use dx\links\models\Domain;

$title = 'Список доменов';

$this->params['breadcrumbs'] = [
	$title,
];

DomainAsset::register($this);

?>
<h1><?= Html::encode($title) ?></h1>

<?php ActiveForm::begin([
	'layout' => 'inline',
	'enableClientValidation' => false,
]); ?>
	<p>
		<?= Html::activeDropDownList($model, 'scheme', Domain::getSchemeNames(), ['class' => 'form-control']) ?>
		<?= Html::activeTextInput($model, 'host', ['class' => 'form-control']) ?>
		<?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
	</p>
<?php ActiveForm::end(); ?>

<?= GridView::widget([
	'dataProvider' => $search->getDataProvider(),
	'tableOptions' => ['class' => 'table table-bordered'],
	'columns' => [
		[
			'attribute' => 'host',
			'header' => 'Домен',
			'value' => function($model, $key, $index, $column) {
				return $model->scheme . '://' . $model->host;
			}
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'options' => ['style' => 'width:50px'],
			'template' => '{delete} {check} {scan}',
			'buttons' => [
				'check' => function($url, $model, $key) {
					if ($model->user->verified)
						return '';
					
					return Html::a('<span class="glyphicon glyphicon-ok"></span>', $url, [
						'title' => 'Проверить',
						'aria-label' => 'Проверить',
						'data-pjax' => 0,
					]);
				},
				'scan' => function($url, $model, $key) {
					if (!$model->user->verified)
						return '';

					return Html::a('<span class="glyphicon glyphicon-search"></span>', $url, [
						'title' => 'Сканировать',
						'aria-label' => 'Сканировать',
						'data-pjax' => 0,
					]);
				},
			],
		],
	],
]) ?>
