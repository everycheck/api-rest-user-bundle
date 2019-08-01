<?php
namespace EveryCheck\UserApiRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;

use EveryCheck\UserApiRestBundle\Entity\User;

class PatchUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $builder->add('active', TextType::class,[
            'constraints' => [
                new Assert\Choice([ 'choices'=>['0','1'],'strict' => true]),
                new Assert\NotNull(),
            ]
        ]);  

        $builder->add('lastPasswordUpdate' , DateTimeType::class, [
            'constraints' => [new Assert\DateTime()],
            'widget' => 'single_text'
        ]); 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,  
            'csrf_protection' => false
        ]);
    }
}