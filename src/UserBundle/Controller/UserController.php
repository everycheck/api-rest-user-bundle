<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;

// use CoreBundle\ControllerTrait\BasicResponseTrait;
// use CoreBundle\ControllerTrait\AccessEntityManagerTrait;
use UserBundle\Entity\User;
use UserBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * @Route("/users/current",
     *     name="get_current_user",
     *     methods={"GET"}
     * )
     */
    public function getCurrentUserAction(Request $request)
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @Route("/users/{id}",
     *     name="get_users",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"GET"}
     * )
     */
    public function getUserAction(Request $request)
    {                
        return $this->get('doctrine.orm.entity_manager')
                    ->getRepository(User::class)
                    ->findByUuid($request->get('id'));
    }

    /**
     * @Route("/users",
     *     name="get_users_list",
     *     methods={"GET"}
     * )
     */
    public function getUsersAction(Request $request,ParamFetcher $paramFetcher)
    {
        return $this->get('doctrine.orm.entity_manager')
                    ->getRepository(User::class)
                    ->findAll();
    }

    /**
     * @Route("/users",
     *     name="post_new_user",
     *     methods={"POST"}
     * )
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid() ==  false)
        {
            return $form;
        }

        $this->get('password_generator')->setUpPassword($user);

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();
    }


}
