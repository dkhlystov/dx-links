<?php

namespace dx\links\assets;

use yii\web\AssetBundle;

class DomainAsset extends AssetBundle
{

	public $css = [
		'domain.css',
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/domain';

		parent::init();
	}

}
