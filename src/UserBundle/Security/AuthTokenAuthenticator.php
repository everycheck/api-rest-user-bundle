<?php
namespace UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
    * Durée de validité du token en secondes, 12 heures
    */
    const TOKEN_VALIDITY_DURATION = 12 * 3600;

    protected $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    public function createToken(Request $request, $providerKey)
    {

        $urlAllowWithoutToken = [
            'POST'=>[
                '/api/auth-tokens',
                '/api/request-magic-link',
                '/api/magic-link/',
                '/api/reset-password/request-token',
                '/api/reset-password/new-password',
                '/api/mailgun/webhook'
            ],
            'GET'=>[
                '/api/logos/',
            ]
        ];            
        foreach ($urlAllowWithoutToken as $method => $AllowedUrls)
        {
            if ($request->getMethod() === $method)
            {
                foreach ($AllowedUrls as $url)
                {
                    if(substr($request->getPathInfo(), 0, strlen($url)) === $url)
                    {
                        return;
                    }
                }
            }    
        }

        $authTokenHeader = $request->headers->get('X-Auth-Token');

        if (!$authTokenHeader) {
            throw new BadCredentialsException('X-Auth-Token header is required');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $authTokenHeader,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof AuthTokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of AuthTokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $authTokenHeader = $token->getCredentials();
        $authToken = $userProvider->getAuthToken($authTokenHeader);

        if (!$authToken || !$this->isTokenValid($authToken) || !$authToken->getUser()->isActive()) {
            throw new BadCredentialsException('Invalid authentication token');
        }

        $user = $authToken->getUser();

        $userProvider->updateLastConnexionDateForUser($user);

        $pre = new PreAuthenticatedToken(
            $user,
            $authTokenHeader,
            $providerKey,
            $user->getRoles()
        );

        // Nos utilisateurs n'ont pas de role particulier, on doit donc forcer l'authentification du token
        $pre->setAuthenticated(true);


        return $pre;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
    * Vérifie la validité du token
    */
    private function isTokenValid($authToken)
    {
        return true;//(time() - $authToken->getCreatedAt()->getTimestamp()) < self::TOKEN_VALIDITY_DURATION;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        $response = new Response(json_encode(array('message' => $exception->getMessageKey())),401);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}