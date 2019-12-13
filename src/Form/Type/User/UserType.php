<?php
namespace App\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\User;
use App\Entity\Organization;

class UserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class)
            ->add('organization', EntityType::class, [
                "class" => Organization::class,
                'choice_label' => fn($organization) => $organization->getName(),
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}