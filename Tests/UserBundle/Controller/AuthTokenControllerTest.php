<?php
namespace EveryCheck\ApiRestUserBundle\Tests\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use EveryCheck\ApiRestUserBundle\Controller\AuthTokenController;
use EveryCheck\ApiRestUserBundle\Entity\User;
use EveryCheck\ApiRestUserBundle\Entity\AuthToken;

class AuthTokenControllerTest extends TestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider data_postAuthTokensAction
     */
    public function test_postAuthTokensAction(string $response,bool $formValid, $user,bool $validPassword)
    {    
        $request = $this->buildRequest('{"test":"test"}');
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($user,'findOneByUsername')     ],
            ['form.factory'                , $e , $this->buildForm($formValid)                             ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse) ],
            ['security.password_encoder'   , $e , $this->buildEncoder($validPassword)                                 ]
        ];
        $parameters = [];
        $container = $this->buildContainer($services,$parameters);

        $authTokenController = new AuthTokenController();
        $authTokenController->setContainer($container);

        $response = $authTokenController->postAuthTokensAction($request,$kind='basic');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_postAuthTokensAction()
    {
        return [
            ['formError'   , false , null     , false ],
            ['formError'   , false , null     , true  ],
            ['formError'   , false , new User , true  ],
            ['formError'   , false , new User , false ],
            ['unauthorized', true  , null     , false ],
            ['unauthorized', true  , null     , true  ],
            ['unauthorized', true  , new User , false ],
            ['created'     , true  , new User , true  ]
        ];
    }

    /**
     * @dataProvider data_deleteAuthTokensAction
     */
    public function test_deleteAuthTokensAction(string $response, $token)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($token,'findOneByUuid')        ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse) ],
        ];
        $parameters = [];
        $container = $this->buildContainer($services,$parameters);


        $authTokenController = new AuthTokenController();
        $authTokenController->setContainer($container);

        $response = $authTokenController->deleteAuthTokenAction($id = 'not really used due to mocking');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_deleteAuthTokensAction()
    {
        return [
            ['deleted'   , null ],
            ['deleted'   , new AuthToken ],
        ];
    }



}
