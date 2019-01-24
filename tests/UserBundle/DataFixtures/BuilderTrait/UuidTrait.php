<?php 
namespace Tests\UserBundle\DataFixtures\BuilderTrait;

trait UuidTrait
{
    public function rewriteUuid(array $entityClasses)
    {
        foreach ($entityClasses as $class)
        {
            $entities = $this->manager->getRepository($class)->findAll();
            foreach ($entities as $entity) {
                $uuid = sprintf('%03d00000-0000-4000-a000-000000000000',$entity->getId());
                $entity->setUuid(\Ramsey\Uuid\Uuid::fromString($uuid));
                $this->manager->persist($entity);
            }
        }
        $this->manager->flush();
    }
}