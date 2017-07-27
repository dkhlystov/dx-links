<?php

namespace dx\links\assets;

use yii\web\AssetBundle;

class ConfirmityAsset extends AssetBundle
{

	public $css = [
		'confirmity.css',
		'chosen.css',
		'bootstrap-chosen.css',
	];

	public $js = [
		'confirmity.js',
		'chosen.jquery.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/confirmity';

		parent::init();
	}

}
