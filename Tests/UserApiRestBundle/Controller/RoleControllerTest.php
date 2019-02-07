<?php
namespace EveryCheck\UserApiRestBundleTests\UserApiRestBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use EveryCheck\UserApiRestBundle\Controller\RoleController;
use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;
use EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\Controller\ControllerTestTrait;
class RoleControllerTest extends TestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider data_postRoleAction
     */
    public function test_postRoleAction(bool $formValid,string $response, $user,$requestGetContentCallCount)
    {    
        $request = $this->buildRequest('{"test":"test"}',$requestGetContentCallCount);
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[            
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($user,'findOneByUuid')               ],
            ['form.factory'                , $e , $this->buildForm($formValid)                                   ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse)       ],
        ];
        $container = $this->buildContainer($services);

        $roleController = new RoleController();
        $roleController->setContainer($container);

        $response = $roleController->postRoleAction($request,$id = 'not really used due to mocking');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_postRoleAction()
    {
        return [
            [false  ,'notFound'  , null     , 0 ],
            [true   ,'notFound'  , null     , 0 ],
            [false  ,'formError' , new User , 1 ],
            [true   ,'created'   , new User , 1 ]
        ];
    }


    /**
     * @dataProvider data_deleteRoleAction
     */
    public function test_deleteRoleAction(string $response, $role)
    {    
        $expectedResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $e = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services =[            
            ['doctrine.orm.entity_manager' , $e , $this->buildEntityManager($role,'findOneByUuid')               ],
            ['response'                    , $e , $this->buildResponseBuilder($response,$expectedResponse)       ],
        ];
        $container = $this->buildContainer($services);

        $roleController = new RoleController();
        $roleController->setContainer($container);

        $response = $roleController->deleteRoleAction($id = 'not really used due to mocking');

        $this->assertEquals($response,$expectedResponse);
    }    

    public function data_deleteRoleAction()
    {
        return [
            ['deleted' , null         ],
            ['deleted' , new UserRole ],
        ];
    }

}