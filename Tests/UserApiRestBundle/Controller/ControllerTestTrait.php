<?php 
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\Controller;

use EveryCheck\UserApiRestBundle\Entity\User;

trait ControllerTestTrait
{
    protected function buildContainer($services,$parameters = array())
    {
        $contrainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMethods(['get','set','has','initialized','getParameter','hasParameter','setParameter'])
            ->getMock();

        $contrainer->method('has')->willReturn(true);
        $contrainer->method('get')->will($this->returnValueMap($services));
        $contrainer->method('getParameter')->will($this->returnValueMap($parameters));

        return $contrainer;
    }

    protected function buildEntityManager(array $repoMethod = ['findOneByUsername'=>null], int $flushCount = null)
    {
        $repo = $this->getMockBuilder('stdClass')
                     ->setMethods(array_keys($repoMethod))
                     ->getMock();

        foreach($repoMethod as $key => $value)
		{
			$repo->method($key)->willReturn($value);
		}

        $em = $this->getMockBuilder('stdClass')
            ->setMethods(['getRepository','persist','flush','remove'])
            ->getMock();

        if($flushCount !== null)
		{
			$em->expects($this->exactly($flushCount))->method('flush')->willReturn(null);
		}

		$em->method('getRepository')->willReturn($repo);

        return $em;
    }

    protected function buildForm(array $responses)
    {   
        $forms = [];

        foreach($responses as $response)
        {
            $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                     ->disableOriginalConstructor()
                     ->setMethods(['submit','isValid'])
                     ->getMock();
            $form->expects($this->once())->method('isValid')->willReturn($response);
            $forms[] = $form;
        }
        
        $formFactory = $this->getMockBuilder('stdClass')
                            ->setMethods(['create'])
                            ->getMock();

       if(!empty($forms))
       {
          $formFactory->expects($this->exactly(count($responses)))->method('create')->willReturnOnConsecutiveCalls(...$forms);      
       }
        
        return $formFactory;      
    }

    protected function buildResponseBuilder($method,$return=null,$argument = null)
    {       
        $builder = $this->getMockBuilder('stdClass')
                        ->disableOriginalConstructor()
                        ->setMethods([$method, 'setSerializationGroups'])
                        ->getMock();
        if(empty($argument))
        {
            $builder->expects($this->exactly(1))->method($method)->willReturn($return);
        }
        else
        {
            $builder->expects($this->exactly(1))->method($method)->with($argument)->willReturn($return);
        }
        
        return $builder;
    }

    protected function buildEncoder(bool $return)
    {       
        $encoder = $this->getMockBuilder('stdClass')
                        ->setMethods(['isPasswordValid'])
                        ->getMock();
        $encoder->method('isPasswordValid')->willReturn($return);
        return $encoder;
    }

    protected function buildRequest($data, $callCount = 1)
    {       
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                        ->disableOriginalConstructor()
                        ->setMethods(['getContent'])
                        ->getMock();  
        $request->expects($this->exactly($callCount))->method('getContent')->willReturn($data);
        return $request;
    }

    protected function buildTokenStorage($user,$countCall=1)
    {
        $token = $this->getMockBuilder('stdClass')
                      ->setMethods(['getUser'])
                      ->getMock();  
        $token->expects($this->exactly($countCall))->method('getUser')->willReturn($user);

        $tokenStorage = $this->getMockBuilder('stdClass')
                             ->setMethods(['getToken'])
                             ->getMock();  
        $tokenStorage->expects($this->exactly($countCall))->method('getToken')->willReturn($token);

        return $tokenStorage;
    }

    protected function buildPasswordGenerator()
    {       
        $generator = $this->getMockBuilder('stdClass')
                        ->setMethods(['setUpPassword'])
                        ->getMock();
        return $generator;
    }

    protected function buildEventDispatcher()
    {
        $eventDispatcher = $this->getMockBuilder('stdClass')
                     ->setMethods(['dispatch'])
                     ->getMock();
        return $eventDispatcher;
    }

    protected function buildAuthorizationChecker($countCall = 0)
    {
        $authorizationChecker = $this->getMockBuilder('stdClass')
                     ->setMethods(['isGranted'])
                     ->getMock();   
        $authorizationChecker->expects($this->exactly($countCall))->method('isGranted')->willReturn(true);

        return $authorizationChecker;
    }

    protected function getUser($format = 'now'): User
    {
        $user = new User();
        $user->setLastPasswordUpdate(new \DateTime($format));
        return $user;
    }


}
