<?php
namespace App\Service\Media;

use App\Entity\MediaFile;
use App\Entity\Organization;
use App\Entity\Ticket;
use App\Entity\User;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager{
	/**
	 * @var string
	 */
	private $mediaFilesDir;

	/**
	 * @var string
	 */
	private $currentPath;

	public function __construct($mediaFilesDir){
		$this->mediaFilesDir = $mediaFilesDir;
		$this->currentPath = $this->path();
	}

	public function path(?DateTimeInterface $dt = null){
		if(!isset($dt)) $dt = new \DateTime();
		$dir = $this->mediaFilesDir . DIRECTORY_SEPARATOR . $dt->format('Y') . DIRECTORY_SEPARATOR . $dt->format('m');
		if(!is_dir($dir)){
			if(file_exists($dir)) unlink($dir);
			if(!mkdir($dir, 0644, true)) return false;
		}
		return $dir;
	}

	public function uploadToMediaFile(UploadedFile $uploadedFile, MediaFile $mediaFile){
		if(!$this->currentPath) throw new \Exception('Media upload path is not defined');
		$size = $uploadedFile->getSize();
		$mimeType = $uploadedFile->getMimeType();
		$originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
		$safeName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
		$newFilename = "$safeName-" . uniqid() . ".{$uploadedFile->guessExtension()}";
		try{
			$newPath = $uploadedFile->move($this->currentPath, $newFilename);
		}catch(FileException $e){
			throw new \Exception("Failed to move $originalFilename into {$this->currentPath}");
		}
		$mediaFile->setName($safeName);
		$mediaFile->setPath($newPath);
		$mediaFile->setSize($size);
		$mediaFile->setMimeType($mimeType);
		$mediaFile->setTimestamp(new \DateTime());
		return true;
	}
}