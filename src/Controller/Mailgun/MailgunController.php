<?php
namespace App\Controller\Mailgun;

use App\Repository\UserRepository;
use App\Service\EmailOrganizationMapper;
use App\Service\MailgunMessages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;


class MailgunController extends AbstractController{
	/**
	 * @Route("/mailgun/inboundMessage", name="mailgun_inboundMessage", methods={"POST"})
	 */
	public function inboundMessage(Request $request, MailgunMessages $mailgun, EmailOrganizationMapper $mapper, EntityManagerInterface $entity_manager, UserRepository $user_repository){
		$post_data = $request->request->all();
		$ticket = $mailgun->makeTicketFromInboundMessage($post_data, $entity_manager);
		if($organization = $mapper->findOrganization($ticket->getEmail(), $user_repository)){
			$ticket->setOrganization($organization);
			$entity_manager->flush();
		}
		return new JsonResponse(['ticket'=>$ticket ? $ticket->getId() : false]);
	}
}