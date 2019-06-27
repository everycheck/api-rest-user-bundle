<?php

namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EveryCheck\UserApiRestBundle\Entity\ResetPasswordRequest;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class,[
            'constraints' =>  [ 
                new Assert\NotBlank(),
                new Assert\Email(["checkMX" => true])
            ]
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ResetPasswordRequest::class,
            'csrf_protection' => false
        ));
    }

}
