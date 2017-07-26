<?php

use yii\bootstrap\Progress;
use yii\helpers\Html;
use yii\helpers\Url;
use dx\links\assets\ScanAsset;

$title = 'Сканировать домен';

$this->params['breadcrumbs'] = [
	['label' => 'Список доменов', 'url' => ['index']],
	$title,
];

ScanAsset::register($this);

$query = clone $dataProvider->query;
$ready = $query->andWhere(['not', ['status' => null]])->count();
$total = $dataProvider->getTotalCount();
$p = 0;
if ($total > 0)
	$p = $ready / $total * 100;

?>
<h1><?= Html::encode($title) ?> <small><?= Html::encode($domain->getDomain()) ?></small></h1>
<p>
	<?= Html::button('Сканировать', ['class' => 'btn btn-primary scan-button', 'data-url' => Url::toRoute(['scan-url', 'domain_id' => $domain->id])]) ?>
</p>
<p>
	<?= Progress::widget([
		'percent' => $p,
		'options' => ['class' => 'scan-progress progress-striped'],
	]) ?>
</p>
<?= $this->render('scan/list', ['dataProvider' => $dataProvider, 'total' => $total]) ?>
