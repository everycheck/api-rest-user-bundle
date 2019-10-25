<?php
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use EveryCheck\UserApiRestBundle\Controller\AuthTokenController;
use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\AuthToken;

class AuthTokenControllerTest extends TestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider data_postAuthTokensAction
     */
    public function test_postAuthTokensAction(string $response,array $formValid,bool $validPassword, array $repoMethods, int $flushCount)
    {    
        $request = $this->buildRequest('{"test":"test"}', count($formValid));
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($repoMethods, $flushCount)     ],
            ['form.factory'                , $e , $this->buildForm($formValid)                             ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse) ],
            ['security.password_encoder'   , $e , $this->buildEncoder($validPassword)                      ],
			['password_generator'          , $e, $this->buildPasswordGenerator()                           ]
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
            ['formError'   , [false]      , false, ['findOneByUsername'=>null]                                            , 0],
            ['formError'   , [false]      , true , ['findOneByUsername'=>null]                                            , 0],
            ['formError'   , [false]      , true , ['findOneByUsername'=>$this->getUser()]                                , 0],
            ['formError'   , [false]      , false, ['findOneByUsername'=>$this->getUser()]                                , 0],
            ['badRequest'  , [true]       , false, ['findOneByUsername'=>null]                                            , 0],
            ['badRequest'  , [true]       , true , ['findOneByUsername'=>null]                                            , 0],
            ['badRequest'  , [true]       , false, ['findOneByUsername'=>$this->getUser()]                                , 0],
            ['formError'   , [true, false], true , ['findOneByUsername'=>$this->getUser("-92 days")]                      , 0],
			['created'     , [true, true] , true , ['findOneByUsername'=>$this->getUser("-92 days"),'findOneByUser'=>null], 2],
            ['created'     , [true]       , true , ['findOneByUsername'=>$this->getUser(),'findOneByUser'=>null]          , 1],
			['created'     , [true]       , true , ['findOneByUsername'=>$this->getUser(),'findOneByUser'=>['test']]      , 0],
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
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager(['findOneByUuid'=>$token])        ],
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
