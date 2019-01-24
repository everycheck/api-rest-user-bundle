<?php
namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use EveryCheck\ApiRest\Utils\ResponseBuilder;


use UserBundle\Entity\Credentials;
use UserBundle\Entity\User;
use UserBundle\Form\CredentialsType;

class AuthTokenController extends Controller
{
    public function __construct()
    {
        $this->response = $response  = new ResponseBuilder($this->get('jms_serializer'));
        $this->em = $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @Route("/auth-tokens",
     *     name="post_auth_tokens",
     *     methods={"POST"}
     * )
     */
    public function postAuthTokensAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);
        $form->submit($request->request->all());

        if ($form->isValid() ==  false)
        {
            return $this->response->formError($form);
        }

        $user = $this->em->getRepository(User::class)->findOneByUsername($credentials->getLogin());

        if (!$user || !$user->isActive()) 
        {
            return $this->response->unauthorized();
        }

        $encoder = $this->get('security.password_encoder');
        if ( $encoder->isPasswordValid($user, $credentials->getPassword()) == false) 
        {
            return $this->response->unauthorized();
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($user);

        $this->em->persist($authToken);
        $this->em->flush();

        return $this->response->ok($authToken);
    }

    /**
     * @Route("/auth-tokens/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="delete_auth_tokens",
     *     methods={"POST"}
     * )
     */
    public function deleteAuthTokenAction(Request $request,$id)
    {        
        $authToken = $this->em->getRepository(AuthToken::class)->find($id);
        if (empty($authToken))
        {
            return $this->response->forbiddenAcl();
        }

        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();
        if (empty($connectedUser) || $connectedUser instanceof User)
        {
            return $this->response->forbiddenAcl();
        }
        
        if($authToken->getUser()->getId() === $connectedUser->getId())
        {
            return $this->response->forbiddenAcl();
        }           

        $this->em->remove($authToken);
        $this->em->flush();

        return $this->response->deleted();
    }
}