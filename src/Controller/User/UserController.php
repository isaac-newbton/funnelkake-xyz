<?php
namespace App\Controller\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\User;
use App\Form\Type\User\UserType;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController {

    /**
     * @Route("/users/list", name="users_list")
     */
    public function usersList(){
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render("admin/user/list.html.twig", [
            "users" => $users
        ]);
    }

    /**
     * @Route("/users/add", name="users_add")
     */
    public function addUser(Request $request){
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ){
            // email them a password reset link at some point
            return $this->redirectToRoute("users_list");
        }
        return $this->render("admin/user/form.html.twig", [
            "form" => $form->createView()
        ]);
    }
    
    /**
     * @Route("/users/edit/{id}", name="users_edit", requirements={"id"="\d+"})
     */
    public function editUser(Request $request, int $id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if ($user){
            $form = $this->createForm(UserType::class, $user);
            
            $form->handleRequest($request);
            if ( $form->isSubmitted() && $form->isValid() ){
                $user = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                // TODO: email them a password reset link ...at some point
                return $this->redirectToRoute("users_list");
            }
            return $this->render("admin/user/form.html.twig", [
                "form" => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute("users_list");
        }
    }

    /**
     * @Route("/users/delete/{id}", name="users_delete", requirements={"id"="\d+"})
     */
    public function deleteUser(Request $request, int $id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if ($user){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            // TODO: notify user of account deletion?
            return $this->redirectToRoute("users_list");
            return $this->redirectToRoute("users_list");
        }
    }

}