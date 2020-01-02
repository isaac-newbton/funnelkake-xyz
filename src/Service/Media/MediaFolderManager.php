<?php
namespace App\Service\Media;

use DateTimeInterface;

class MediaFolderManager{
	/**
	 * @var string
	 */
	private $mediaFilesDir;

	public function __construct($mediaFilesDir){
		$this->mediaFilesDir = $mediaFilesDir;
	}

	public function path(?DateTimeInterface $dt){
		if(!isset($dt)) $dt = new \DateTime();
		$dir = $this->mediaFilesDir . PATH_SEPARATOR . $dt->format('Y') . PATH_SEPARATOR . $dt->format('m');
		if(!is_dir($dir)){
			if(file_exists($dir)) unlink($dir);
			if(!mkdir($dir, 0644, true)) return false;
		}
		return $dir;
	}
}