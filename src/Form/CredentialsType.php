<?php
namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EveryCheck\UserApiRestBundle\Entity\Credentials;

use Symfony\Component\Validator\Constraints as Assert;

class CredentialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login',TextType::class,[
        "constraints" => [
                new Assert\NotBlank()
            ]
        ]);
        $builder->add('password',TextType::class,[
        "constraints" => [
                new Assert\NotBlank()
            ]
        ]);
        $builder->add('newPassword',TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Credentials::class,
            'csrf_protection' => false
        ]);
    }
}