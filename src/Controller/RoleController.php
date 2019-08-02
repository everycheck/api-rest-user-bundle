<?php
namespace EveryCheck\UserApiRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;
use EveryCheck\UserApiRestBundle\Form\PostRoleType;
use EveryCheck\UserApiRestBundle\Form\PatchRoleType;

class RoleController extends Controller
{
    /**
     * @Route("/users/{id}/role",
     *     name="post_user_role",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"POST"}
     * )
     * @IsGranted("ROLE_ROLE_CREATE")
     */
    public function postRoleAction(Request $request,$id)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');

        $role = new UserRole();
        $form = $this->createForm(PostRoleType::class, $role);

        $user = $this->em->getRepository(User::class)->findOneByUuid($id);
        if(empty($user))
        {
            return $this->get('response')->notFound();
        }
        $role->setUser($user);

        $requestData = json_decode($request->getContent(), $responseAsArray=true);

        $form->submit($requestData);
        if ($form->isValid() == false)
        {
            return $this->get('response')->formError($form);
        }

        $postedRight = [];
        $postedRight['name'] = $requestData['name'];
        if(array_key_exists('creator', $requestData) && $requestData['creator'] == '1') $postedRight['creator'] = true;
        if(array_key_exists('reader' , $requestData) && $requestData['reader' ] == '1') $postedRight['reader' ] = true;
        if(array_key_exists('updator', $requestData) && $requestData['updator'] == '1') $postedRight['updator'] = true;
        if(array_key_exists('deletor', $requestData) && $requestData['deletor'] == '1') $postedRight['deletor'] = true;
        $this->denyAccessUnlessGranted('role', $postedRight);

        $this->em->persist($role);
        $this->em->flush();
        
        return $this->get('response')->created($role);
    }

    /**
     * @Route("/users/roles/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="patch_user_role",
     *     methods={"PATCH"}
     * )     
     * @IsGranted("ROLE_ROLE_UPDATE")
     */
    public function patchRoleAction(Request $request,$id)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');

        $role = $this->em->getRepository(UserRole::class)->findOneByUuid($id);

        if(empty($role))
        {
            return $this->get('response')->notFound();
        }

        if($this->get('security.token_storage')->getToken()->getUser() == $role->getUser())
        {
            return $this->get("response")->forbidden('cannot edit itself');
        }

        $requestData = json_decode($request->getContent(), $responseAsArray=true);

        $form = $this->createForm(PatchRoleType::class, $role);
        $form->submit($requestData,$clearMissing=false);
        if ($form->isValid() == false)
        {
            return $this->get('response')->formError($form);
        }

        $requestData['name'] = $role->getName();
        $this->denyAccessUnlessGranted('role', $requestData);
        
        $this->em->persist($role);
        $this->em->flush();
        
        return $this->get('response')->ok($role);
    }

    /**
     * @Route("/users/roles/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="delete_user_role",
     *     methods={"DELETE"}
     * )     
     * @IsGranted("ROLE_ROLE_DELETE")
     */
    public function deleteRoleAction($id)
    {             
        $em = $this->get('doctrine.orm.entity_manager');
        $role = $em->getRepository(UserRole::class)->findOneByUuid($id);

        if(empty($role))
        {
            return $this->get('response')->deleted();
        }

        if($this->get('security.token_storage')->getToken()->getUser() == $role->getUser())
        {
            return $this->get("response")->forbidden('cannot delete itself');
        } 
        
        $deletedRight['name'] = $role->getName();
        if($role->getCreator()) $deletedRight['creator'] = true;
        if($role->getReader ()) $deletedRight['reader' ] = true;
        if($role->getUpdator()) $deletedRight['updator'] = true;
        if($role->getDeletor()) $deletedRight['deletor'] = true;

        $this->denyAccessUnlessGranted('role', $deletedRight);

        $em->remove($role);
        $em->flush();

        return $this->get('response')->deleted();

    }
}