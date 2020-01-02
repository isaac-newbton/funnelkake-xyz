<?php

namespace App\Form\Type\MediaFile;

use App\Entity\MediaFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaFileType extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, ?array $options){
		$builder
			->add('name', TextType::class, [
				'label'=>'Name/Title',
				'required'=>false
			])
			->add('file', FileType::class, [
				'label'=>'File',
				'mapped'=>false,
				'required'=>true,
				'constraints'=>[
					new File([
						'maxSize'=>'8mi',
						'mimeTypes'=>[
							'image/jpeg',
							'image/png',
						],
						'mimeTypesMessage'=>'Please upload a valid file type (JPEG, PNG)'
					])
				]
			])
			->add('submit', SubmitType::class);
		;
	}

	public function configureOptions(OptionsResolver $resolver){
		$resolver->setDefaults([
			'data_class'=>MediaFile::class
		]);
	}
}