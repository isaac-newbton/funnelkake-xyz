<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\Organization;

class OrganizationType extends abstractType {

    protected $params;

    public function __construct(ParameterBagInterface $params){
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            // ->add('organization', EntityType::class)
            ->add('name', TextType::class)
            ->add('ticketEmailSlug', TextType::class, [
                'label' => 'Ticket Email',
                'help' => "Email for @{$this->params->get('app.ticket_email_domain')} that accepts new tickets for this company. I.E. \"myname\" accepts tickets as myname@{$this->params->get('app.ticket_email_domain')}"
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Organization::class,
        ]);
    }
}