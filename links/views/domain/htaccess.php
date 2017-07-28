<?php

use yii\helpers\Html;
use dx\links\utils\Htaccess;

$title = 'Формирование .htaccess';

$this->params['breadcrumbs'] = [
	['label' => 'Список доменов', 'url' => ['index']],
	['label' => 'Соответствие страницв', 'url' => ['conformity', 'src_id' => $src->id, 'dest_id' => $dest->id]],
	$title,
];

?>
<h1><?= Html::encode($title) ?></h1>

<form class="form-inline">
	<?= Html::hiddenInput('src_id', $src->id) ?>
	<?= Html::hiddenInput('dest_id', $dest->id) ?>
	<div class="form-group">
		<label>Домен</label>
		<?= Html::textInput('domain', $domain, ['class' => 'form-control', 'placeholder' => 'http://example.com']) ?>
	</div>
	<?= Html::submitButton('Установить', ['class' => 'btn btn-primary']) ?>
</form>

<br>

<?= Html::textArea(null, Htaccess::make($src, $dest, $domain), ['class' => 'form-control', 'rows' => 10]) ?>
