<?php
namespace EveryCheck\UserApiRestBundle\Security;

use Symfony\Component\HttpFoundation\Request;

class AuthTokenWhiteListUrl
{

    const WHITE_LIST_URL = [
        'POST'=>[
            '/tokens/basic',
            '/tokens/jwt',
            '/users/password/reset/request',
            '/users/password/reset'
        ],
    ];  

    static public function isWhiteListed(Request $request) : bool
    {
        foreach (self::WHITE_LIST_URL as $method => $AllowedUrls)
        {
            if ($request->getMethod() === $method)
            {
                foreach ($AllowedUrls as $url)
                {
                    if(substr($request->getPathInfo(), 0, strlen($url)) === $url)
                    {
                        return true;
                    }
                }
            }    
        }
        return false;
    }
}