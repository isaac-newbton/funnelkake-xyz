<?php
namespace App\Controller\MediaFile;

use App\Entity\MediaFile;
use App\Form\Type\MediaFile\MediaFileType;
use App\Repository\MediaFileRepository;
use App\Service\Media\MediaManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
			$mediaManager->uploadToMediaFile($uploadedFile, $mediaFile);
			if($name = $form['name']->getData()){
				$mediaFile->setName($name);
			}

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($mediaFile);
			$entityManager->flush();
			return $this->redirectToRoute('files_list');
		}

		return $this->render('media/upload.html.twig', [
			'form'=>$form->createView()
		]);
	}

	/**
	 * @Route("/files", name="files_list")
	 */
	public function list(MediaFileRepository $repository){
		$files = $repository->findAll();
		return $this->render('media/list.html.twig', [
			'files'=>$files
		]);
	}

	/**
	 * @Route("/files/view/{encoded}", name="files_view")
	 */
	public function view(string $encoded, MediaFileRepository $repository, MediaManager $mediaManager){
		$file = $repository->findOneByEncodedUuid($encoded);
		if($file){
			$fullPath = $this->getParameter('app.media_files_dir') . DIRECTORY_SEPARATOR . $file->getPath();
			if(file_exists($fullPath)){
				return new BinaryFileResponse($fullPath);
			}
		}
		return new Response('File not found', 404);
	}
}