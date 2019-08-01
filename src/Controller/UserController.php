<?php

namespace EveryCheck\UserApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use EveryCheck\ApiRest\Utils\ResponseBuilder;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\ResetPasswordRequest;
use EveryCheck\UserApiRestBundle\Entity\ResetPassword;
use EveryCheck\UserApiRestBundle\Form\PostUserType;
use EveryCheck\UserApiRestBundle\Form\PatchUserType;
use EveryCheck\UserApiRestBundle\Form\ResetPasswordRequestType;
use EveryCheck\UserApiRestBundle\Form\ResetPasswordType;
use EveryCheck\UserApiRestBundle\Event\UserEvent;
use EveryCheck\UserApiRestBundle\Event\PasswordEvent;


class UserController extends Controller
{

    /**
     * @Route("/users/current",
     *     name="get_current_user",
     *     methods={"GET"}
     * )
     */
    public function getCurrentUserAction()
    {        
        return $this->get('response')->ok($this->get('security.token_storage')->getToken()->getUser());
    }

    /**
     * @Route("/users/{id}",
     *     name="get_users",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"GET"}
     * )
     * @IsGranted("ROLE_USER_READ")
     */
    public function getUserAction($id)
    {            
        $user = $this->get('doctrine.orm.entity_manager')->getRepository(User::class)->findOneByUuid($id);
        if(empty($user)) return $this->get('response')->notFound();

        return $this->get('response')->ok($user);
    }

    /**
     * @Route("/users",
     *     name="get_users_list",
     *     methods={"GET"}
     * )
     * @IsGranted("ROLE_USER_READ")
     */
    public function getUsersAction()
    {
        return $this->get('response')->ok(
            $this->get('doctrine.orm.entity_manager')
                    ->getRepository(User::class)
                    ->findAll()
            );
    }

    /**
     * @Route("/users/{id}",
     *     name="patch_users",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"PATCH"}
     * )
     * @IsGranted("ROLE_USER_UPDATE")
     */
    public function patchUserAction(Request $request,$id)
    {          
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneByUuid($id);
        if(empty($user))
        {
            return $this->get('response')->notFound();
        }
        $requestData = json_decode($request->getContent(), $responseAsArray=true);

        $form = $this->createForm(PatchUserType::class, $user);
        $form->submit($requestData,$clearMissing=false);
        if ($form->isValid() ==  false)
        {
            return $this->get('response')->formError($form);
        }

        $em->persist($user);
        $em->flush();

        return $this->get('response')->ok($user);
    }

    /**
     * @Route("/user",
     *     name="post_new_user",
     *     methods={"POST"}
     * )
     * @IsGranted("ROLE_USER_CREATE")
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(PostUserType::class, $user);

        $form->submit(json_decode($request->getContent(), true),true);

        if ($form->isValid() ==  false)
        {
            return $this->get('response')->formError($form);
        }

        $this->get('password_generator')->setUpPassword($user);

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(UserEvent::POST_NAME,new UserEvent($user));

        return $this->get('response')->created($user);
    }

    /**
     * @Route("/users/{id}",
     *     name="delete_users",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"DELETE"}
     * )
     * @IsGranted("ROLE_USER_DELETE")
     */
    public function deleteUserAction($id)
    {          
        $em =    $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneByUuid($id);
        
        if(empty($user))
        {
            return $this->get('response')->deleted();
        }

        $this->get('event_dispatcher')->dispatch(UserEvent::DELETE_NAME,new UserEvent($user));

        $em->remove($user);
        $em->flush();

        return $this->get('response')->deleted();
    }
}
