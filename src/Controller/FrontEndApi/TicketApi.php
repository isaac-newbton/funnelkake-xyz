<?php

namespace App\Controller\FrontEndApi;

use App\Entity\StopwatchAwareTrait as EntityStopwatchAwareTrait;
use App\Entity\Ticket;
use App\Entity\User;
use App\Service\UserRole\UserRoleHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// TODO: Add conditions to the following endpoints to prevent them from being returned when requested from anywhere other than the current server

class TicketApi extends AbstractController {

	use EntityStopwatchAwareTrait;
	/**
	 * @Route("/api/ticket/organization/users/add/{ticketId}", methods={"POST"})
	 */
	public function addTicketUser(int $ticketId, Request $request) {
		$ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($ticketId);
		$userData = json_decode($request->getContent());

		if ($ticket && $userData){
			// return new JsonResponse($userData);
				$entityManager = $this->getDoctrine()->getManager();
				$ticket->addUser($this->getDoctrine()->getRepository(User::class)->find($userData->id));
				$entityManager->persist($ticket);
				$entityManager->flush();
				return new JsonResponse("added $userData->username");
		}
	return new JsonResponse("No change");
	}

	/**
	 * @Route("/api/ticket/organization/users/remove/{ticketId}", methods={"POST"})
	 */
	public function removeTicketUser(int $ticketId, Request $request) {
		$ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($ticketId);
		$userData = json_decode($request->getContent());

		if ($ticket && $userData){
			// return new JsonResponse($userData);
				$entityManager = $this->getDoctrine()->getManager();
				$ticket->removeUser($this->getDoctrine()->getRepository(User::class)->find($userData->id));
				$entityManager->persist($ticket);
				$entityManager->flush();
				return new JsonResponse("removed $userData->username");
		}
	return new JsonResponse("No change");
	}

	/**
	 * @Route("/api/ticket/organization/users/{ticketId}", methods={"GET"})
	 */
	public function getTicketOrganizationUsers(int $ticketId){
		// TODO: This following lines should occur completely in a service that we call i.e. $userRoleHandler->getOrganizationUsers()
		if (count($organizationUsers = $this->getDoctrine()->getRepository(Ticket::class)->find($ticketId)->getOrganization()->getUsers()) > 0){
			$response = [];
			foreach ($organizationUsers as $user){
				$response[] = [
					"id" => $user->getId(),
					"email" => $user->getEmail(),
					"username" => $user->getUsername(),
				];
			}	
			return new JsonResponse($response);
		} else {
			return new JsonResponse([]);
		}
	}

	/**
	 * @Route("/api/ticket/staff/users/{ticketId}", methods={"GET"})
	 */
	public function getTicketstaffUsers(int $ticketId){

		// TODO: This following lines should occur completely in a service that we call i.e. $userRoleHandler->getStaffUsers()
		if ($this->isGranted("ROLE_STAFF")) {

			$userRoleHandler = new UserRoleHandler(new RoleHierarchy($this->getParameter('security.role_hierarchy.roles')));
			$users = $this->getDoctrine()->getRepository(User::class)->findAll();
			$response = [];
			foreach($users as $user){
				foreach($user->getRoles() as $user_role){
					if (in_array('ROLE_STAFF', $userRoleHandler->getInheritedRoles($user_role))){
						$response[] = [
							"id" => $user->getId(),
							"email" => $user->getEmail(),
							"username" => $user->getUsername(),
						];
						continue;
					}
				}
			}
			return new JsonResponse($response);
		} else {
			return new JsonResponse([]);
		}
	}

	/**
	 * @Route("/api/ticket/users/assigned/{id}", methods={"GET"})
	 */
	public function getTicketUsers(int $id){
		if ($ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id)){
			$response = [];
			foreach($ticket->getUsers() as $user){
				$response[] = [
					"id" => $user->getId(),
					"email" => $user->getEmail(),
					"username" => $user->getUsername(),
				];
			}
			return new JsonResponse($response);
		} else {
			return new JsonResponse([]);
		}
	}
}