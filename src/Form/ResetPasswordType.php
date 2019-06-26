<?php

namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EveryCheck\UserApiRestBundle\Entity\ResetPassword;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;


class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('token', TextType::class, [
            "constraints" => [
                new Assert\NotBlank()
            ]
        ]);
        $builder->add('password', TextType::class, [
            "constraints" => [
                new Assert\NotBlank(),
                new Assert\Length([
                    "min" => 6,
                    "max" => 50,
                    "minMessage" => "Your password must be at least {{ limit }} characters long",
                    "maxMessage" => "Your password cannot be longer than {{ limit }} characters"
                ])
            ]
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ResetPassword::class,
            'csrf_protection' => false
        ));
    }

}
