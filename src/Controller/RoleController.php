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

        $form->submit(json_decode($request->getContent(), true),true);
        if ($form->isValid() == false)
        {
            return $this->get('response')->formError($form);
        }

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
        $entity = $em->getRepository(UserRole::class)->findOneByUuid($id);
        $em->remove($entity);
        $em->flush();

        return $this->get('response')->deleted();

    }
}