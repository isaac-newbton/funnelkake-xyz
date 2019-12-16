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

use App\Service\Timestamp\TimestampHandler;

class TicketController extends AbstractController {

    /**
     * @Route("/tickets/submit", name="tickets_form")
     */
    public function ticketSubmit(Request $request, TimestampHandler $timestamp){
        $ticket = new Ticket();
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
            $ticket->setTimestamp($timestamp->createTimestamp());

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
    public function ticketList(){
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findAll();
        return $this->render("admin/ticket/list.html.twig", [
            "tickets" => $tickets
        ]);
    }

    /**
     * @Route("tickets/view/{id}", name="tickets_single")
     * @IsGranted("ROLE_ADMIN")
     */
    public function viewTicket(int $id, Request $request, TimestampHandler $timestamp){
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->remove("ticket");
        $form->remove("timestamp");
        $form->remove("task");
        $form->remove("user");

        $form->handleRequest($request);

        if ($form ->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $comment->setTicket($ticket);
            $comment->setTimestamp($timestamp->createTimestamp());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
        }

        return $this->render("admin/ticket/single.html.twig", [
            "ticket" => $ticket,
            "form" => $form->createView()
        ]);
    }
}