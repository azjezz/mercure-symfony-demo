<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psl\Type;

/**
 * @template T of object
 *
 * @extends EntityRepository<T>
 */
abstract class AbstractRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @param class-string<T> $class
     */
    public function __construct(ManagerRegistry $registry, string $class)
    {
        $manager = Type\object(EntityManagerInterface::class)->assert($registry->getManagerForClass($class));
        $metadata = Type\object(ClassMetadata::class)->assert($manager->getClassMetadata($class));

        parent::__construct($manager, $metadata);
    }

    /**
     * Delete the given object.
     *
     * @param T $object
     *
     * @throws ORMException
     */
    public function delete(object $object): void
    {
        $manager = $this->getEntityManager();

        $manager->remove($object);
        $manager->flush();
    }

    /**
     * @param T $object
     *
     * @throws ORMException
     */
    public function save(object $object): void
    {
        $manager = $this->getEntityManager();

        $manager->persist($object);
        $manager->flush();
    }
}
