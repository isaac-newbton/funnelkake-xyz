<?php
namespace App\Service;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

class MailgunMessages{
	public function makeTicketFromInboundMessage($post_data, EntityManagerInterface $entity_manager) : Ticket{
		$ticket = new Ticket();
		$ticket->setRawJson($post_data);
		$ticket->setSubject($post_data['Subject']);
		$ticket->setContent($post_data['body-html']);
		$ticket->setTimestamp(new \DateTime($post_data['Date']));
		$ticket->setEmail($post_data['From']);
		$entity_manager->persist($ticket);
		$entity_manager->flush();
		return $ticket;
	}
}