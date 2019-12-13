<?php
namespace App\Service;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;

class MailgunMessages{
	public function makeTicketFromInboundMessage($post_data, EntityManagerInterface $entity_manager) : Ticket{
		$ticket = new Ticket();
		$ticket->setRawJson($post_data);
		$ticket->setSubject('Test');
		$ticket->setContent('<p>Test ticket content</p>');
		$ticket->setTimestamp(new \DateTime());
		$ticket->setEmail('test@example.com');
		$entity_manager->persist($ticket);
		$entity_manager->flush();
		return $ticket;
	}
}