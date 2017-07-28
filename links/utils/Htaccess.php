<?php

namespace dx\links\utils;

use yii\helpers\ArrayHelper;
use dx\links\models\Domain;

class Htaccess
{

	public static function make(Domain $src, Domain $dest, $domain = null)
	{
		$urls = $src->getUrls()->joinWith(['conformities c' => function ($query) use ($dest) {
			$query->andWhere(['c.domain_id' => $dest->id]);
		}], true, 'INNER JOIN')->all();

		if (empty($domain))
			$domain = $src->getDomain();

		$s = '';
		foreach ($urls as $from) {
			$to = ArrayHelper::getValue($from->conformities, 0);
			if ($to === null || $to->url == $from->url)
				continue;

			$s .= 'Redirect 301 ' . $from->url . ' ' . $domain . $to->url . "\n";
		}

		return $s;
	}

}
