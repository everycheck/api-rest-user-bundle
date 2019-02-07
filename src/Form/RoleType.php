<?php
namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('name', TextType::class ,[
            'constraints' =>  [ new NotBlank() ]
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