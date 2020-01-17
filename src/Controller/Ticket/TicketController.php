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
use App\Form\Type\Ticket\TicketStaffType;
use App\Form\Type\Comment\CommentType;
use App\Form\Type\MediaFile\MediaFileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\Type\Ticket\TicketOrganizationType;
use App\Form\Type\Ticket\TicketUsersType;
use App\Service\Email\EmailServiceHandler;
use App\Service\Media\MediaManager;
use App\Service\Timestamp\TimestampHandler;
use App\Service\UserRole\UserRoleHandler;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

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
     * @IsGranted("ROLE_USER")
     */
    public function viewOrganizationTickets(int $id){
        $organization = $this->getDoctrine()->getRepository(Organization::class)->find($id);
        if ($this->isGranted("ROLE_STAFF") || $this->getUser()->getOrganization() === $organization) {
            $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findBy(["organization" => $organization]);

            return $this->render("admin/ticket/list.html.twig", [
                "tickets" => $tickets,
                "organization" => $organization
                ]);
        } else {
            return new Response("Access Denied");
        }
    }

    /**
     * @Route("/tickets/view/{id}", name="tickets_single")
     * @IsGranted("ROLE_USER")
     */

    public function viewTicket(int $id, Request $request, TimestampHandler $timestamp, EmailServiceHandler $emailServiceHandler, MediaManager $mediaManager){
        $twig_forms = [];
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);
        if ($this->isGranted("ROLE_STAFF") || $this->getUser()->getOrganization() == $ticket->getOrganization()){

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

            $recipients = [];
            foreach ($ticket->getUsers() as $user){
                if ($user != $comment->getUser()) $recipients[] = $user->getEmail();
            }
            $emailServiceHandler->sendEmail(
                $recipients,
                null,
                null,
                "Ticket updated: {$ticket->getSubject()}",
                "ticketComment.html.twig",
                [
                    "ticket" => $ticket,
                    "comment" => $comment
                ]
                );
            return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
        }
        $twig_forms['commentForm'] = $commentForm->createView();

        $ticketOrganizationForm = $this->createForm(TicketOrganizationType::class);
        $ticketOrganizationForm->add('organization', EntityType::class, [
            'class' => Organization::class,
            'choice_label' => function ($o) {
                return $o->getName();
            }
        ]);
        $ticketOrganizationForm->add('ticket', HiddenType::class, ['mapped' => false]);
        $ticketOrganizationForm->add('submit', SubmitType::class);

        $ticketOrganizationForm->handleRequest($request);
        if ($ticketOrganizationForm->isSubmitted() && $ticketOrganizationForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();

            $ticketData = $this->getDoctrine()->getRepository(Ticket::class)->find(intval($ticketOrganizationForm->get('ticket')->getData()));
            $organization = $ticketOrganizationForm->get('organization')->getData();

            if ($ticketData && $organization) {
                // remove users when we assign ticket to a new organization
                // TODO: this shouldn't remove staff members
                foreach($ticket->getUsers() as $user){
                    $ticket->removeUser($user);
                }

                $ticketData->setOrganization($organization);
                $entityManager->persist($ticketData);
                $entityManager->flush();
                return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
            }
        }
        $twig_forms['ticketOrganizationForm'] = $ticketOrganizationForm->createView();

        $ticketStaffForm = $this->createForm(TicketStaffType::class, $ticket);
        $ticketStaffForm->add('submit', SubmitType::class);
        // TODO: This following 11 lines should occur completely in a service that we call i.e. $userRoleHandler->getStaffUsers()
        $userRoleHandler = new UserRoleHandler(new RoleHierarchy($this->getParameter('security.role_hierarchy.roles')));
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $staff_users = [];
        foreach($users as $u){
            foreach($u->getRoles() as $user_role){
                if (in_array('ROLE_STAFF', $userRoleHandler->getInheritedRoles($user_role))){
                    $staff_users[] = $u;
                    continue;
                }
            }
        }
        $ticketStaffForm->add('users', EntityType::class, [
            'class' => User::class,
            'choices' => $staff_users,
            'choice_label' => function ($u) {
                return $u->getEmail();
            },
            'multiple' => true,
            'expanded' => true
        ]);

        $ticketStaffForm->handleRequest($request);
        if ($ticketStaffForm->isSubmitted() && $ticketStaffForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $ticketData = $ticketStaffForm->getData();
            $entityManager->persist($ticketData);
            $entityManager->flush();
            return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
        }
        $twig_forms['ticketStaffForm'] = $ticketStaffForm->createView();

        if ($ticket->getOrganization()){
            $ticketUsersForm = $this->createForm(TicketUsersType::class, $ticket);
            $ticketUsersForm->add('users', EntityType::class, [
                'class' => User::class,
                'choices' => $ticket->getOrganization()->getUsers(),
                'choice_label' => function ($u) {
                    return $u->getEmail();
                },
                'multiple' => true,
                'expanded' => true
            ]);
            $ticketUsersForm->add('submit', SubmitType::class);
            $twig_forms['ticketUsersForm'] = $ticketUsersForm->createView();

            $ticketUsersForm->handleRequest($request);
            if ($ticketUsersForm->isSubmitted() && $ticketUsersForm->isValid()){
                $entityManager = $this->getDoctrine()->getManager();
                $ticketData = $ticketUsersForm->getData();
                $entityManager->persist($ticketData);
                $entityManager->flush();
                return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
            }

            $mediaFileForm = $this->createForm(MediaFileType::class);
            $twig_forms['mediaFileForm'] = $mediaFileForm->createView();

            $mediaFileForm->handleRequest($request);
            if ($mediaFileForm->isSubmitted() && $mediaFileForm->isValid()){
                $entityManager = $this->getDoctrine()->getManager();
                $mediaFile = $mediaFileForm->getData();
                $mediaFile->addTicket($ticket);
                $mediaFile->setUser($this->getUser());
                $mediaFile->setOrganization($ticket->getOrganization());

                // TODO: This should happen in the formType since it will always occur when uploading files?
                /**
                 * @var UploadedFile $uploadedFile
                 **/
                $uploadedFile = $mediaFileForm->get('file')->getData();
                $mediaManager->uploadToMediaFile($uploadedFile, $mediaFile);

                if($name = $mediaFileForm->get('name')->getData()){
                    $mediaFile->setName($name);
                }
            
                $entityManager->persist($mediaFile);
                $entityManager->flush();
                return $this->redirectToRoute("tickets_single", ["id" => $ticket->getId() ]);
            }
        }

        
        $render_args = ["ticket" => $ticket];
        return $this->render("admin/ticket/single.html.twig", array_merge($render_args, $twig_forms));
        } else {
            return new Response("Access denied");
        }
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