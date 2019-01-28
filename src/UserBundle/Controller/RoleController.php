<?php
namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use UserBundle\Entity\User;
use UserBundle\Entity\UserRole;
use UserBundle\Form\RoleType;

class RoleController extends Controller
{
    /**
     * @Route("/users/{id}/role",
     *     name="post_user_role",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     methods={"POST"}
     * )
     */
    public function postRoleAction(Request $request,$id)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');

        $role = new UserRole();
        $form = $this->createForm(RoleType::class, $role);

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
     *     name="delete_user_role",
     *     methods={"DELETE"}
     * )
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