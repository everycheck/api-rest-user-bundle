<?php

namespace EveryCheck\UserApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use EveryCheck\ApiRest\Utils\ResponseBuilder;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\ResetPasswordRequest;
use EveryCheck\UserApiRestBundle\Entity\ResetPassword;
use EveryCheck\UserApiRestBundle\Form\UserType;
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
     */
    public function getUserAction($id)
    {             
        return $this->get('response')->ok(
            $this->get('doctrine.orm.entity_manager')
                    ->getRepository(User::class)
                    ->findOneByUuid($id)
            );
    }

    /**
     * @Route("/users",
     *     name="get_users_list",
     *     methods={"GET"}
     * )
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
     * @Route("/users",
     *     name="post_new_user",
     *     methods={"POST"}
     * )
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

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
     */
    public function deleteUserAction($id)
    {          
        $em =    $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneByUuid($id);
        $em->remove($user);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(UserEvent::DELETE_NAME,new UserEvent($user));

        return $this->get('response')->deleted();
    }

    /**
     * @Route("/reset-password/request-token", name="post_reset_password_request", methods={"POST"})
     */
    public function postRequestTokenAction(Request $request)
    {
        $resetPasswordRequest = new ResetPasswordRequest();
        $form = $this->createForm(ResetPasswordRequestType::class, $resetPasswordRequest);
        $postedResetPasswordRequest = json_decode($request->getContent(), true);
        $form->submit($postedResetPasswordRequest);

        if(!$form->isValid()) 
        {
            return $this->get('response')->formError($form);
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository(User::class)->findOneByEmail($resetPasswordRequest->getEmail());

        if(!$user || !$user->isActive()) 
        { 
            return $this->get('response')->created($resetPasswordRequest);
        }

        $token = [
           "uuid" => $user->getUuidAsString(),
           "email" => $resetPasswordRequest->getEmail(),
           "exp" => date_create('+5 minutes')->format('U')
        ];

        $JWTtoken = \Firebase\JWT\JWT::encode($token, $this->getParameter('secret'));

        $this->get('event_dispatcher')
             ->dispatch(PasswordEvent::PASSWORD_RESET_REQUEST_NAME, new PasswordEvent($JWTtoken));

        return $this->get('response')->created(["jwt"=>$JWTtoken]);
    }

    /**
     * @Route("/reset-password/new-password", name="post_reset_password", methods={"POST"})
     */
    public function postResetPasswordAction(Request $request)
    {
        $resetPassword = new ResetPassword();
        $form = $this->createForm(ResetPasswordType::class, $resetPassword);
        $postedResetPassword = json_decode($request->getContent(), true);
        $form->submit($postedResetPassword);

        if(!$form->isValid()) 
        {
            return $this->get('response')->formError($form);
        }

        try
        {
            $token = \Firebase\JWT\JWT::decode($resetPassword->getToken(),$this->getParameter("secret"),array('HS256'));
            $em = $this->get('doctrine.orm.entity_manager');

            $user = $em->getRepository(User::class)->findOneByUuid($token->uuid);

            if(empty($user))
            {
                return $this->get('response')->badRequest([
                    "children" => [
                        "token" => [
                            "errors" => "The token is invalid (no user)"
                        ],
                        "password" => []
                    ]
                ]);
            }

            if($user->getLastPasswordUpdate()->getTimestamp() > $token->exp)
            {
                return $this->get('response')->badRequest([
                    "children" => [
                        "token" => [
                            "errors" => "The token is invalid (password already reset)"
                        ],
                        "password" => []
                    ]
                ]);
            }
        }
        catch(\Exception $e)
        {
            return $this->get('response')->badRequest([
                "children" => [
                    "token" => [
                        "errors" => "The token is invalid (invalid token)"
                    ],
                    "password" => []
                ]
            ]);
        }

        $encoder = $this->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $resetPassword->getPassword());
        $user->setPassword($encoded);
        $em->flush();
        return $this->get('response')->created($user);
    }
}
