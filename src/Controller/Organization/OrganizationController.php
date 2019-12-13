<?php
namespace App\Controller\Organization;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Organization;
use App\Form\Type\OrganizationType;

/**
 * Require ROLE_ADMIN for every controller method in this class
 * @IsGranted("ROLE_ADMIN")
 */
class OrganizationController extends abstractController {

    /**
     * @Route("/organizations/view", name="organizations_list")
     */
    public function getOrganizations(){
        $organizations = $this->getDoctrine()->getRepository(Organization::class)->findAll();
        return $this->render("/admin/organization/list.html.twig", [
            'organizations' => $organizations
        ]);
    }

    /**
     * @Route("/organizations/view/{id}", name="organizations_single", requirements={"id"="\d+"})
     */
    public function getSingleOrganization(int $id){
        return new Response("list single org by id: $id");
    }

    /**
     * @Route("/organizations/add", name="organizations_add")
     */
    public function addOrganization(Request $request ){
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);

        // process form submit
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            $organization = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($organization);
            $entityManager->flush();

            return $this->redirectToRoute("organizations_list");
        }

        return $this->render("/admin/organization/form.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/organizations/edit/{id}", name="organizations_edit", requirements={"id"="\d+"})
     */
    public function editOrganization(int $id, Request $request){
        // show edit by id or by entity ???
        $organization = $this->getDoctrine()->getRepository(Organization::class)->find($id);
        $form = $this->createForm(OrganizationType::class, $organization);

         // process form submit
         $form->handleRequest($request);
         if ( $form->isSubmitted() && $form->isValid() ) {
             $organization = $form->getData();
 
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($organization);
             $entityManager->flush();
 
             return $this->redirectToRoute("organizations_list");
         }
 
         return $this->render("/admin/organization/form.html.twig", [
             'form' => $form->createView(),
             'organization' => $organization
         ]);
    }

    /**
     * @Route("/organizations/delete/{id}", name="organizations_delete", requirements={"id"="\d+"})
     */
    public function deleteOrganization(int $id){
        // show edit by id or by entity ???
        $organization = $this->getDoctrine()->getRepository(Organization::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($organization);
        $entityManager->flush();

        return $this->redirectToRoute("organizations_list");
    }
}