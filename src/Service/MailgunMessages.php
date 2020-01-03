<?php
namespace App\Service;

use App\Entity\Ticket;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class MailgunMessages{
	public function makeTicketFromInboundMessage($post_data, EntityManagerInterface $entity_manager) : Ticket{
		$ticket = new Ticket();
		if($post_data['body-html']){
			$comment = new Comment();
			$comment->setContent($post_data['body-html']);
			$comment->setTimestamp(new \DateTime());
			$entity_manager->persist($comment);
		}
		$ticket->addComment($comment);
		$ticket->setSubject($post_data['Subject']);
		$ticket->setEmail($post_data['From']);
		$entity_manager->persist($ticket);
		$entity_manager->flush();
		return $ticket;
	}
}