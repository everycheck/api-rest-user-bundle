<?php
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use EveryCheck\UserApiRestBundle\Controller\UserController;
use EveryCheck\UserApiRestBundle\Entity\User;

class UserControllerTest extends TestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider data_getCurrentUserAction
     */
    public function test_getCurrentUserAction(string $response, $user)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['security.token_storage' , $e , $this->buildTokenStorage($user)                                ],
            ['response'               , $e , $this->buildResponseBuilder($response,$expectedResponse,$user) ],
        ];
        $container = $this->buildContainer($services);

        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->getCurrentUserAction();

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_getCurrentUserAction()
    {
        return [
            ['ok'   , $this->getUser() ]
        ];
    }


    /**
     * @dataProvider data_getUserAction
     */
    public function test_getUserAction(string $response, $user, $id)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($user,'findOneByUuid')               ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse,$user) ],
        ];
        $container = $this->buildContainer($services);

        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->getUserAction($id);

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_getUserAction()
    {
        return [
            ['notFound'   , null             , 'someUselessId'],
            ['ok'         , $this->getUser() , 'someUselessId']
        ];
    }


    /**
     * @dataProvider data_getUsersAction
     */
    public function test_getUsersAction(string $response, $users, $id)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($users,'findAll')                     ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse,$users) ],
        ];
        $container = $this->buildContainer($services);

        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->getUsersAction($id);

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_getUsersAction()
    {
        return [
            ['ok'   , [$this->getUser()] , 'someUselessId']
        ];
    }

    /**
     * @dataProvider data_patchUsersAction
     */
    public function test_patchUsersAction(string $response,array $formValid, $user, $isEditingCurrentUser)
    {    
        $request = $this->buildRequest('{"test":"test"}',empty($formValid)?0:1);
        $expectedCallTokenStorage = empty($user)?0:1;
        $tokenStorageUser = $isEditingCurrentUser ? $user :  null;
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($user,'findOneByUuid')                      ],
            ['form.factory'                , $e , $this->buildForm($formValid)                                          ],
            ['security.token_storage'      , $e , $this->buildTokenStorage($tokenStorageUser, $expectedCallTokenStorage)],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse)              ],
        ];
        $container = $this->buildContainer($services);

        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->patchUserAction($request,'some_useless_id');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_patchUsersAction()
    {
        return [
            ['notFound'   , []      , null      , false  ],
            ['forbidden'  , []      , new User  , true   ],
            ['formError'  , [false] , new User  , false  ],
            ['ok'         , [true]  , new User  , false  ],
        ];
    }

    /**
     * @dataProvider data_postUsersAction
     */
    public function test_postUsersAction(string $response,array $formValid)
    {    
        $request = $this->buildRequest('{"test":"test"}');
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager()                              ],
            ['form.factory'                , $e , $this->buildForm($formValid)                             ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse) ],
            ['password_generator'          , $e , $this->buildPasswordGenerator()                          ],
            ['event_dispatcher'            , $e , $this->buildEventDispatcher()                            ]

        ];
        $container = $this->buildContainer($services);

        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->postUsersAction($request);

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_postUsersAction()
    {
        return [
            ['formError'   , [false]   ],
            ['created'     , [true]    ],
        ];
    }


    /**
     * @dataProvider data_deleteUserAction
     */
    public function test_deleteUserAction(string $response, $user,$isDeletingCurrentUser)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $expectedCallTokenStorage = empty($user)?0:1;
        $tokenStorageUser = $isDeletingCurrentUser ? $user :  null;
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($user,'findOneByUuid')        ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse) ],
            ['security.token_storage'      , $e , $this->buildTokenStorage($tokenStorageUser, $expectedCallTokenStorage)],
            ['event_dispatcher'            , $e , $this->buildEventDispatcher()                            ]
            
        ];
        $parameters = [];
        $container = $this->buildContainer($services,$parameters);


        $userController = new UserController();
        $userController->setContainer($container);

        $response = $userController->deleteUserAction($id = 'not really used due to mocking');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_deleteUserAction()
    {
        return [
            ['deleted'   , null             , false ],
            ['forbidden' , $this->getUser() , true  ],
            ['deleted'   , $this->getUser() , false ],
        ];
    }
}
