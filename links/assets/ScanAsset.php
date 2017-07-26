<?php

namespace dx\links\assets;

use yii\web\AssetBundle;

class ScanAsset extends AssetBundle
{

	public $css = [
		'scan.css',
	];

	public $js = [
		'scan.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/scan';

		parent::init();
	}

}
