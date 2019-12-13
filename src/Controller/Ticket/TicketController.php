<?php
namespace App\Controller\Ticket;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Form\Type\Ticket\TicketType;
use App\Entity\Ticket;
use App\Entity\Organization;
use App\Entity\User;

class TicketController extends AbstractController {

    /**
     * @Route("/tickets/submit", name="ticket_form")
     */
    public function ticketSubmit(Request $request){
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ){

            $ticket = $form->getData();
            
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
             
            // set the timestamp
            $datetime = new \DateTime();
            $timezone = new \DateTimeZone('America/New_York');
            $datetime->setTimezone($timezone);

            $ticket->setTimestamp($datetime);

            $entityManager =  $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute("ticket_received", [
                "ticket" => $ticket
            ]);
        }

        return $this->render("ticket/form.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/tickets/received", name="ticket_received")
     */
    public function ticketReceived(){
        return new Response("received");
    }

    /**
     * @Route("/tickets/view", name="ticket_list")
     * @
     */
    public function ticketList(){
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findAll();
        return $this->render("ticket/list.html.twig", [
            "tickets" => $tickets
        ]);
    }
}