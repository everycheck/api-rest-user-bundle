<?php
namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EveryCheck\UserApiRestBundle\Entity\RenewPassword;
use Symfony\Component\Validator\Constraints as Assert;

class RenewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login',TextType::class);
        $builder->add('password',TextType::class);
        $builder->add('newPassword',TextType::class,[
        "constraints" => [
                new Assert\NotBlank([
                    "message" => "Field required since password is expired"
                ]),
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
        $resolver->setDefaults([
            'data_class' => RenewPassword::class,
            'csrf_protection' => false
        ]);
    }
}