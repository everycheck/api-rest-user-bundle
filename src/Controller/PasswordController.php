<?php

namespace EveryCheck\UserApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use EveryCheck\ApiRest\Utils\ResponseBuilder;

use EveryCheck\UserApiRestBundle\Entity\ResetPasswordRequest;
use EveryCheck\UserApiRestBundle\Entity\ResetPassword;
use EveryCheck\UserApiRestBundle\Form\ResetPasswordRequestType;
use EveryCheck\UserApiRestBundle\Form\ResetPasswordType;
use EveryCheck\UserApiRestBundle\Event\PasswordEvent;

class PasswordController extends Controller
{
 	/**
     * @Route("/users/password/reset/request", name="post_reset_password_request", methods={"POST"})
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
     * @Route("/users/password/reset", name="post_reset_password", methods={"POST"})
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