<?php

use yii\helpers\Html;

$s = urldecode($model->url);
$url = Html::tag('div', Html::encode($s), ['class' => 'list-url', 'title' => $s]);

$s = $model->title;
$title = Html::tag('div', Html::encode($s), ['class' => 'list-title', 'title' => $s]);

echo Html::tag('div', $url . $title, ['class' => 'list-container', 'data-id' => $model->id]);
