<?php
namespace App\Controller\Mailgun;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailgunController extends AbstractController{
	/**
	 * @Route("/mailgun/inboundMessage", name="mailgun_inboundMessage", methods={"POST"})
	 */
	public function inboundMessage(Request $request){
		$post_data = $request->request->all();
		return new JsonResponse($post_data);
	}
}