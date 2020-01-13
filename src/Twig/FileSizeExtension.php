<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FileSizeExtension extends AbstractExtension{
	public function getFilters(){
		return [
			new TwigFilter('format_bytes', [$this, 'formatBytes'])
		];
	}

	public function formatBytes($bytes, $precision = 1){
		$size = ['B', 'K', 'M', 'G', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}
}