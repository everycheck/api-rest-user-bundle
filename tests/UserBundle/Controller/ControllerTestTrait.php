<?php 
namespace Tests\UserBundle\Controller;


trait ControllerTestTrait
{
    protected function buildContainer($services,$parameters = array())
    {
        $contrainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMethods(['get','set','has','initialized','getParameter','hasParameter','setParameter'])
            ->getMock();

        $contrainer->method('get')->will($this->returnValueMap($services));
        $contrainer->method('getParameter')->will($this->returnValueMap($parameters));

        return $contrainer;
    }

    protected function buildEntityManager($entitiesReturned=null,$repoMethod = 'findOneByUsername')
    {
        $repo = $this->getMockBuilder('stdClass')
                     ->setMethods([$repoMethod])
                     ->getMock();
        $repo->method($repoMethod)->willReturn($entitiesReturned);

        $em = $this->getMockBuilder('stdClass')
            ->setMethods(['getRepository','persist','flush','remove'])
            ->getMock();

        $em->method('getRepository')->willReturn($repo);

        return $em;
    }

    protected function buildForm(bool $isValid)
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                     ->disableOriginalConstructor()
                     ->setMethods(['submit','isValid'])
                     ->getMock();
        $form->method('isValid')->willReturn($isValid);

        $formFactory = $this->getMockBuilder('stdClass')
                            ->setMethods(['create'])
                            ->getMock();
        $formFactory->method('create')->willReturn($form);
        return $formFactory;      
    }

    protected function buildResponseBuilder($method,$return=null,$argument = null)
    {       
        $builder = $this->getMockBuilder('stdClass')
                        ->disableOriginalConstructor()
                        ->setMethods([$method])
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

    protected function buildRequest($data)
    {       
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                        ->disableOriginalConstructor()
                        ->setMethods(['getContent'])
                        ->getMock();  
        $request->expects($this->exactly(1))->method('getContent')->willReturn($data);
        return $request;
    }

    protected function buildTokenStorage($user)
    {
        $token = $this->getMockBuilder('stdClass')
                      ->setMethods(['getUser'])
                      ->getMock();  
        $token->expects($this->exactly(1))->method('getUser')->willReturn($user);

        $tokenStorage = $this->getMockBuilder('stdClass')
                             ->setMethods(['getToken'])
                             ->getMock();  
        $tokenStorage->expects($this->exactly(1))->method('getToken')->willReturn($token);

        return $tokenStorage;
    }

    protected function buildPasswordGenerator()
    {       
        $generator = $this->getMockBuilder('stdClass')
                        ->setMethods(['setUpPassword'])
                        ->getMock();
        return $generator;
    }


}