<?php
namespace EveryCheck\UserApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use EveryCheck\UserApiRestBundle\Entity\Credentials;
use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\AuthToken;
use EveryCheck\UserApiRestBundle\Entity\RenewPassword;
use EveryCheck\UserApiRestBundle\Form\RenewPasswordType;
use EveryCheck\UserApiRestBundle\Form\CredentialsType;

class AuthTokenController extends Controller
{
    const MAX_PASSWORD_VALIDITY_DAYS = 90;
    /**
     * @Route("/tokens/{kind}",
     *     name="post_auth_tokens",
     *     requirements={"kind" = "basic|jwt"},
     *     methods={"POST"}
     * )
     */
    public function postAuthTokensAction(Request $request,$kind)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');

        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit(json_decode($request->getContent(), true),true);
        if($form->isValid() == false)
        {
            return $this->get('response')->formError($form);
        }

        $user = $this->em->getRepository(User::class)->findOneByUsername($credentials->getLogin());
        if(!$user || !$user->isActive()) 
        {
            return $this->get('response')->badRequest('Login or password invalid');
        }

        $encoder = $this->get('security.password_encoder');
        if($encoder->isPasswordValid($user, $credentials->getPassword()) == false) 
        {
            return $this->get('response')->badRequest('Login or password invalid');
        }
        
        if($this->isPasswordExpired($user)) 
        {
            $renewPassword = new RenewPassword();
            $form = $this->createForm(RenewPasswordType::class, $renewPassword);

            $form->submit(json_decode($request->getContent(), true));
            if($form->isValid() == false)
            {
                return $this->get('response')->formError($form);
            }

            $this->get('password_generator')->setUpPassword($user,$renewPassword->getPassword());
            $this->em->flush();
        }

        $authToken = null;
        switch($kind)
        {
            case 'basic' : {
                $authToken = new AuthToken();
                $authToken->setValue(base64_encode(random_bytes(50)));
                $authToken->setCreatedAt(new \DateTime('now'));
                $authToken->setUser($user);

                $this->em->persist($authToken);
                $this->em->flush();
                break;
            }
            case 'jwt' : {
                $token = array(
                    "uuid" => $user->getUuidAsString(),
                    "exp" => date_create('+1 day')->format('U')
                );
                $authToken = ['jwt'=>\Firebase\JWT\JWT::encode($token, $this->getParameter('secret'))];
            }
        }
        return $this->get('response')->created($authToken);
    }

    /**
     * @Route("/tokens/basic/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="delete_auth_tokens",
     *     methods={"DELETE"}
     * )
     */
    public function deleteAuthTokenAction($id)
    {               
        $em = $this->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(AuthToken::class)->findOneByUuid($id);
        $em->remove($entity);
        $em->flush();

        return $this->get('response')->deleted();

    }

    private function isPasswordExpired(User $user): bool
    {
        $interval = $user->getLastPasswordUpdate()->diff(new \DateTime());
        $days = intval($interval->format('%a'));
        return abs($days) > self::MAX_PASSWORD_VALIDITY_DAYS;
    }
}