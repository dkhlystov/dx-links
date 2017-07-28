<?php

namespace dx\links\assets;

use yii\web\AssetBundle;

class ConformityAsset extends AssetBundle
{

	public $css = [
		'conformity.css',
		'chosen.css',
		'bootstrap-chosen.css',
	];

	public $js = [
		'conformity.js',
		'chosen.jquery.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/conformity';

		parent::init();
	}

}
