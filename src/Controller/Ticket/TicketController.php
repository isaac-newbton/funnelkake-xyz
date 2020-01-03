<?php
namespace App\Controller\Ticket;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Form\Type\Ticket\TicketType;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\Organization;
use App\Entity\User;
use App\Form\Type\Comment\CommentType;
use App\Service\Ticket\TicketHandler;
use App\Service\Timestamp\TimestampHandler;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class TicketController extends AbstractController {

    /**
     * @Route("/tickets/submit", name="tickets_form")
     */
    public function ticketSubmit(Request $request, TimestampHandler $timestamp){
        $ticket = new Ticket();
        $ticket->setStatus(Ticket::STATUS_OPEN);
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ){
            // set the tickets organization
            if ($this->getUser()){
                // user is logged in already - just use their email
                $organization = $this->getUser()->getOrganization();
            } else {
                // is there an organization containing a user with that email? 
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'email' => $form->get('email')->getData()
                ]);
                
                if ($user){
                    // found a matching organizaiton for the supplied email
                    $organization = $user->getOrganization();
                } else {
                    // otherwise check if there is an organization with the same name as the senders email domain
                    $domain = explode('@', $form->get('email')->getData())[1]; // remove user@
                    $organization = $this->getDoctrine()->getRepository(Organization::class)->findOneBy([
                        'name' => explode('.', $domain)[0] // get the host from the name
                    ]);

                }
                $ticket->setOrganization($organization ?? null); // $organization can still be null at this point - thats ok!
            }

            $comment = new Comment();
            $comment->setContent($form->get('comment')->getData());
            $comment->setUser($this->getUser() ?? null);
            $comment->setTimestamp($timestamp->createTimestamp());


            $ticket = $form->getData();
            $ticket->addComment($comment);

            $entityManager =  $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute("tickets_received", [
                "ticket" => $ticket
            ]);
        }

        return $this->render("ticket/form.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/tickets/received", name="tickets_received")
     */
    public function ticketReceived(){
        return new Response("received");
    }

    /**
     * @Route("/tickets/list", name="tickets_list")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ticketList(Request $request){
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findBy([
            "status" => !Ticket::STATUS_CLOSED
        ],[
            "status" => "ASC"
        ]);
        $ticketOrganizationForm = $this->createForm(TicketType::class);
        $ticketOrganizationForm->add('organization', EntityType::class, [
            'class' => Organization::class,
            'choice_label' => function ($o) {
                return $o->getName();
            }
        ]);
        $ticketOrganizationForm->add('ticket', HiddenType::class, [
            "mapped" => false,
        ]);

        $ticketOrganizationForm->handleRequest($request);
        // TODO: refactor this nasy bit at some point...
        if ($ticketOrganizationForm->isSubmitted() && $ticketOrganizationForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();

            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find(intval($ticketOrganizationForm->get('ticket')->getData()));
            $organization = $ticketOrganizationForm->get('organization')->getData();

            if ($ticket && $organization) {
                $ticket->setOrganization($organization);
                $entityManager->persist($ticket);
                $entityManager->flush();
            }

            return $this->redirectToRoute("tickets_list");
        }

        return $this->render("admin/ticket/list.html.twig", [
            "tickets" => $tickets,
            "ticketOrganizationForm" => $ticketOrganizationForm->createView(),
        ]);
    }
    
    /**
     * @Route("/tickets/list/closed", name="tickets_list_closed")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ticketListClosed(Request $request){
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findBy([
            "status" => Ticket::STATUS_CLOSED
        ]);
        $ticketOrganizationForm = $this->createForm(TicketType::class);
        $ticketOrganizationForm->add('organization', EntityType::class, [
            'class' => Organization::class,
            'choice_label' => function ($o) {
                return $o->getName();
            }
        ]);
        $ticketOrganizationForm->add('ticket', HiddenType::class, [
            "mapped" => false,
        ]);

        $ticketOrganizationForm->handleRequest($request);
        // TODO: refactor this nasy bit at some point...
        if ($ticketOrganizationForm->isSubmitted() && $ticketOrganizationForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();

            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find(intval($ticketOrganizationForm->get('ticket')->getData()));
            $organization = $ticketOrganizationForm->get('organization')->getData();

            if ($ticket && $organization) {
                $ticket->setOrganization($organization);
                $entityManager->persist($ticket);
                $entityManager->flush();
            }

            return $this->redirectToRoute("tickets_list");
        }

        return $this->render("admin/ticket/list.html.twig", [
            "tickets" => $tickets,
            "ticketOrganizationForm" => $ticketOrganizationForm->createView(),
        ]);
    }

    /**
     * @Route("/tickets/organization/{id}/list", name="tickets_list_organization", requirements={"id"="\d+"})
     */
    public function viewOrganizationTickets(int $id){
        $organization = $this->getDoctrine()->getRepository(Organization::class)->find($id);
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findBy(["organization" => $organization]);

        return $this->render("admin/ticket/list.html.twig", [
            "tickets" => $tickets,
            "organization" => $organization
        ]);
    }

    /**
     * @Route("tickets/view/{id}", name="tickets_single")
     * @IsGranted("ROLE_ADMIN")
     */
    public function viewTicket(int $id, Request $request, TimestampHandler $timestamp){
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->remove("ticket");
        $commentForm->remove("timestamp");
        $commentForm->remove("task");
        $commentForm->remove("user");

        $commentForm->handleRequest($request);
        if ($commentForm ->isSubmitted() && $commentForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $comment = $commentForm->getData();
            $comment->setUser($this->getUser());
            $comment->setTicket($ticket);
            $comment->setTimestamp($timestamp->createTimestamp());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
        }

        $ticketOrganizationForm = $this->createForm(TicketType::class);
        $ticketOrganizationForm->add('organization', EntityType::class, [
            'class' => Organization::class,
            'choice_label' => function ($o) {
                return $o->getName();
            }
        ]);
        $ticketOrganizationForm->add('ticket', HiddenType::class, [
            "mapped" => false,
        ]);

        $ticketOrganizationForm->handleRequest($request);
        // TODO: refactor this nasy bit at some point...
        if ($ticketOrganizationForm->isSubmitted() && $ticketOrganizationForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();

            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find(intval($ticketOrganizationForm->get('ticket')->getData()));
            $organization = $ticketOrganizationForm->get('organization')->getData();

            if ($ticket && $organization) {
                $ticket->setOrganization($organization);
                $entityManager->persist($ticket);
                $entityManager->flush();
            }
            // return $this->redirectToRoute("tickets_list");
        }

        return $this->render("admin/ticket/single.html.twig", [
            "ticket" => $ticket,
            "commentForm" => $commentForm->createView(),
            "ticketOrganizationForm" => $ticketOrganizationForm->createView(),
        ]);
    }

    /**
     * @Route("/ticket/close/{id}", name="tickets_close", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function closeTicket(int $id, TimestampHandler $timestamp){
        $entityManager = $this->getDoctrine()->getManager();
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        if ($ticket){
            $ticket->close();
            $entityManager->persist($ticket);

            $comment = new Comment();
            $comment->setUser($this->getUser());
            $comment->setTimestamp($timestamp->createTimestamp());
            $comment->setTicket($ticket);
            $comment->setContent("Ticket status set to {$ticket->getStatusText()}");
            $entityManager->persist($comment);

            $entityManager->flush();
        }
        return $this->redirectToRoute("tickets_list");
    }

    /**
     * @Route("/ticket/open/{id}", name="tickets_open", requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function openTicket(int $id, TimestampHandler $timestamp){
        $entityManager = $this->getDoctrine()->getManager();
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        if ($ticket){
            $ticket->open();
            $entityManager->persist($ticket);

            $comment = new Comment();
            $comment->setUser($this->getUser());
            $comment->setTimestamp($timestamp->createTimestamp());
            $comment->setTicket($ticket);
            $comment->setContent("Ticket status set to {$ticket->getStatusText()}");
            $entityManager->persist($comment);

            $entityManager->flush();
        }
        return $this->redirectToRoute("tickets_list");
    }
}