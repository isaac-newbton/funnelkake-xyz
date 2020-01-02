<?php
namespace App\Controller\MediaFile;

use App\Entity\MediaFile;
use App\Form\Type\MediaFile\MediaFileType;
use App\Service\Media\MediaManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaFileController extends AbstractController{
	/**
	 * @Route("/files/upload", name="upload_file")
	 */
	public function upload(Request $request, MediaManager $mediaManager){
		$mediaFile = new MediaFile();

		$form = $this->createForm(MediaFileType::class, $mediaFile);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			if($user = $this->getUser()){
				$mediaFile->setUser($user);
				$mediaFile->setOrganization($user->getOrganization());
			}

			/** @var UploadedFile $uploadedFile */
			$uploadedFile = $form['file']->getData();
			$mediaFile->setSize($uploadedFile->getSize());
			$mediaFile->setMimeType($uploadedFile->getMimeType());
			$originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
			$safeName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
			$newFilename = $safeName . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
			try{
				$newPath = $uploadedFile->move($this->getParameter('app.media_files_dir'), $newFilename);
			}catch(FileException $e){
				throw new \Exception("Failed to move $originalFilename to $newFilename");
			}

			if(!$name = $form['name']->getData()){
				$name = $safeName;
			}
			$mediaFile->setName($name);
			$mediaFile->setPath($newPath);
			$mediaFile->setTimestamp(new \DateTime());

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($mediaFile);
			$entityManager->flush();
		}

		return $this->render('media/upload.html.twig', [
			'form'=>$form->createView()
		]);
	}
}