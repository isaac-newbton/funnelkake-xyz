<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeveloperController extends AbstractController{
	/**
	 * @Route("/dev/phpinfo", name="phpinfo")
	 */
	public function phpinfo(){
		// $this->denyAccessUnlessGranted('ROLE_DEVELOPER');
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		return new Response("<html><head><title>PHP INFO</title></head><body>$phpinfo</body></html>");
	}
}