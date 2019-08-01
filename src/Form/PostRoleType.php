<?php
namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;

class PostRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('name', TextType::class ,[
            'constraints' =>  [ new Assert\NotBlank() ]
        ]);  
        $builder->add('creator', TextType::class ,[
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices'=>['0','1'],
                    'strict' => true
                ])
            ]
        ]);  
        $builder->add('reader', TextType::class ,[
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices'=>['0','1'],
                    'strict' => true
                ])
            ]        ]);  
        $builder->add('updator', TextType::class ,[
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices'=>['0','1'],
                    'strict' => true
                ])
            ]
        ]);  
        $builder->add('deletor', TextType::class ,[
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices'=>['0','1'],
                    'strict' => true
                ])
            ]
        ]);
        $builder->add('administrator' , DateTimeType::class, [
            'constraints' => [new Assert\DateTime()],
            'widget' => 'single_text'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRole::class,  
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity(['fields' => ['name','user']]),
           ]
        ]);
    }
}