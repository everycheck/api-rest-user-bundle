<?php
namespace EveryCheck\UserApiRestBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;

class PatchRoleType extends PostRoleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        parent::buildForm($builder,$options);
        $builder->remove('name');  
    }


}