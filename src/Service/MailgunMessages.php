<?php
namespace App\Service;

use App\Entity\Ticket;
use App\Entity\Comment;
use App\Entity\MediaFile;
use App\Service\Media\MediaManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MailgunMessages{
	public function makeTicketFromInboundMessage($post_data, $files_data, EntityManagerInterface $entity_manager, MediaManager $media_manager) : Ticket{
		$ticket = new Ticket();
		if($post_data['body-html']){
			$comment = new Comment();
			$comment->setContent($post_data['body-html']);
			$comment->setTimestamp(new \DateTime());
			$entity_manager->persist($comment);

			if($files_data && !empty($files_data)){
				$comment->setContent('<pre>Files: ' . count($files_data) . PHP_EOL . var_export($files_data, true) . '</pre><br>' . $comment->getContent());
				/**
				 * @var UploadedFile $uploaded_file
				 */
				foreach($files_data as $uploaded_file){
					$file = new MediaFile();
					$media_manager->uploadToMediaFile($uploaded_file, $file);
					$entity_manager->persist($file);
					$ticket->addMediaFile($file);
				}
			}
		}
		$ticket->addComment($comment);
		$ticket->setSubject($post_data['Subject']);
		$ticket->setEmail($post_data['From']);
		$entity_manager->persist($ticket);
		$entity_manager->flush();
		return $ticket;
	}
}