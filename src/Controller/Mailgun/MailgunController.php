<?php
namespace App\Controller\Mailgun;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\MailgunMessages;

class MailgunController extends AbstractController{
	/**
	 * @Route("/mailgun/inboundMessage", name="mailgun_inboundMessage", methods={"POST"})
	 */
	public function inboundMessage(Request $request, MailgunMessages $mailgun, EntityManagerInterface $entity_manager){
		$post_data = $request->request->all();
		$ticket = $mailgun->makeTicketFromInboundMessage($post_data, $entity_manager);
		return new JsonResponse(['ticket'=>$ticket ? $ticket->getId() : false]);
	}
}