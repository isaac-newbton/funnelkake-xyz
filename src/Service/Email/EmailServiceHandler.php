<?php

namespace App\Service\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailServiceHandler {

	private $mailer;

	public function __construct(MailerInterface $mailer) {
		$this->mailer = $mailer;
	}

	public function sendEmail(array $recipients, array $cc = null, array $bcc = null, string $subject, string $template_name, array $context) {

		if (!isset($cc)) $cc = [];
		if (!isset($bcc)) $bcc = [];

		$email = (new TemplatedEmail())
		->addTo(...$recipients)
		->addCc(...$cc)
		->addBcc(...$bcc)
		->subject($subject)
		->from('noreply@funnelkake.co')
		->replyTo('noreply@funnelkake.co')
		->htmlTemplate("email/$template_name")
		->context($context)
		;

        /**
		 * @var Symfony\Component\Mailer\SentMessage $sentEmail
		 */
        $sentEmail = $this->mailer->send($email);
    }
}