<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController{
	/**
	 * @Route("/admin", name="admin_index")
	 */
	public function index(){
		// $this->denyAccessUnlessGranted('ROLE_ADMIN');
		return new Response("<html><head><title>ADMIN</title></head><body>dashboard</body></html>");
	}
}