<?php

use yii\grid\GridView;
use yii\helpers\Html;
use dx\links\assets\ConformityAsset;
use dx\links\models\Url;

$title = 'Соответствие страниц';

$this->params['breadcrumbs'] = [
	['label' => 'Список доменов', 'url' => ['index']],
	$title,
];

ConformityAsset::register($this);

$urls = ['' => ''];
foreach (Url::find()->select(['id', 'status', 'url', 'title'])->where(['domain_id' => $dest->id])->asArray()->all() as $row)
	$urls[$row['id']] = '[' . $row['status'] . '] ' . urldecode($row['url']) . ' ( ' . $row['title'] . ' )';

$view = $this;

?>
<h1><?= Html::encode($title) ?> <small><?= Html::encode($src->getDomain() . ' -> ' . $dest->getDomain()) ?></small></h1>

<!--p>
	<?= Html::checkbox(null, $unfinished, ['id' => 'conformity-unfinished', 'label' => 'Только необработанные']) ?>
</p-->

<p>
	<?= Html::a('Сформировать .htaccess', ['htaccess', 'src_id' => $src->id, 'dest_id' => $dest->id], ['class' => 'btn btn-primary']) ?>
</p>

<?= GridView::widget([
	'id' => 'conformity-list',
	'dataProvider' => $dataProvider,
	'columns' => [
		//status, url, title
		[
			'header' => $src->getDomain(),
			'content' => function($model, $key, $index, $column) use ($view) {
				return $this->render('conformity/item', ['model' => $model]);
			},
		],
		[
			'header' => $dest->getDomain(),
			'content' => function($model, $key, $index, $column) use ($dest) {
				$object = $model->getConformities()->andWhere(['domain_id' => $dest->id])->one();
				if ($object === null)
					return null;

				return $this->render('conformity/item', ['model' => $object]);
			},
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'options' => ['width' => '25px'],
			'template' => '{select}',
			'buttons' => [
				'select' => function($url, $model, $key) {
					return Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-chevron-right']), '#', ['class' => 'conformity']);
				},
			],
		],
	],
]) ?>

<div class="modal list-modal" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<?= Html::dropDownList(null, null, $urls, ['class' => 'form-control']) ?>
			</div>
			<div class="modal-footer">
				<?= Html::button('Отмена', ['class' => 'btn btn-default conformity-cancel']) ?>
				<?= Html::a('Сохранить', ['conformity-set'], ['class' => 'btn btn-primary conformity-set']) ?>
			</div>
		</div>
	</div>
</div>
