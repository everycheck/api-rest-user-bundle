<?php
namespace EveryCheck\ApiRestUserBundle\Security;

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

use EveryCheck\ApiRest\Utils\ResponseBuilder;

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    const TOKEN_VALIDITY_DURATION = 24 * 3600;
    const AUTH_TOKEN_NAME = 'X-Auth-Token';

    public function __construct(HttpUtils $httpUtils,ResponseBuilder $response,$secret)
    {
        $this->httpUtils = $httpUtils;
        $this->response = $response;
        $this->secret = $secret;
    }

    public function createToken(Request $request, $providerKey)
    {
        if(AuthTokenWhiteListUrl::isWhiteListed($request))
        {
            return;
        }

        $authTokenHeader = $request->headers->get($this::AUTH_TOKEN_NAME);

        if (empty($authTokenHeader)) 
        {
            throw new BadCredentialsException($this::AUTH_TOKEN_NAME.' header is required');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $authTokenHeader,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof AuthTokenUserProvider) 
        {
            $errorMessage = sprintf(
                'The user provider must be an instance of AuthTokenUserProvider (%s was given).',
                get_class($userProvider)
            );
            throw new \InvalidArgumentException($errorMessage);
        }

        $authTokenHeader = $token->getCredentials();
        $user=null;
        try
        {   
            $jwtToken = \Firebase\JWT\JWT::decode($authTokenHeader,$this->secret,array('HS256'));
            $user = $userProvider->loadUserByUuid($jwtToken->uuid);  
        }
        catch (\Exception $e)
        {
            $authToken = $userProvider->getAuthToken($authTokenHeader);  
            if (!$authToken || !$authToken->getUser()->isActive()) 
            {
                throw new BadCredentialsException('Invalid authentication token');
            }
            $user = $authToken->getUser();
        }

        $pre = new PreAuthenticatedToken(
            $user,
            $authTokenHeader,
            $providerKey,
            $user->getRoles()
        );

        $pre->setAuthenticated(true);
        return $pre;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->response->unauthorized();
    }
}