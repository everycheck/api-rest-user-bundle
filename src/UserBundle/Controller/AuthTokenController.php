<?php
namespace UserBundle\Controller;

use UserBundle\Entity\FailAuthLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use UserBundle\Form\Type\CredentialsType;
use UserBundle\Entity\AuthToken;
use UserBundle\Entity\Credentials;
use UserBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class AuthTokenController extends Controller
{
    /**  
     * @ApiDoc(
     *    description="Create a new auth-token for user related to credential",
     *    section="auth-token",
     *    input={"class"=CredentialsType::class, "name"=""},
     *    statusCodes = {
     *        201 = "Created with sucess",
     *        400 = "Invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=AuthToken::class},
     *         400 = { "class"=CredentialsType::class, "form_errors"=true, "name" = ""}
     *    })
     * @Rest\View(statusCode=Response::HTTP_CREATED , serializerGroups={"Default","user_detailed","user_name"})
     * @Rest\Post("/auth-tokens")
     */
    public function postAuthTokensAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $ip = $request->getClientIp();

        $form->submit($request->request->all());

        if (!$form->isValid()) 
        {
            return $this->get('form_error_to_array')->getErrorResponse($form);
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('UserBundle:User')->findOneByUsername($credentials->getLogin());

        if($em->getRepository('UserBundle:FailAuthLog')->hasIpMadeTooManyRequest($ip))
        {
            return $this->tooManyRequest();
        }

        if (!$user || !$user->isActive()) 
        {
            return $this->invalidCredentials($user,$ip);
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if (!$isPasswordValid) 
        {;
            return $this->invalidCredentials($user,$ip);
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($user);
        $authToken->setIp($ip);
        $authToken->setUserAgent($request->headers->get('User-Agent'));

        $em->persist($authToken);
        $em->flush();

        return $authToken;
    }



    /**  
     * @ApiDoc(
     *    description="Delete an existing auth-token to unlog user",
     *    section="auth-token",
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/auth-tokens/{id}")
     */
    public function removeAuthTokenAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $authToken = $em->getRepository('UserBundle:AuthToken')->find($request->get('id'));
        /* @var $authToken AuthToken */

        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();

        if ($authToken && $connectedUser instanceof User && $authToken->getUser()->getId() === $connectedUser->getId()) {
            $em->remove($authToken);
            $em->flush();
        }
        else
        {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Id does not match credentials");
        }
    }

    private function invalidCredentials($user,$ip)
    {
        $this->get('doctrine.orm.entity_manager')
                ->getRepository('UserBundle:FailAuthLog')
                ->logFailedAuth($user,$ip);
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }

    private function tooManyRequest(){
        return \FOS\RestBundle\View\View::create(['message' => 'Please wait'], Response::HTTP_TOO_MANY_REQUESTS);
    }
}