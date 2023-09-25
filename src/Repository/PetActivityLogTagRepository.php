<?php

namespace App\Repository;

use App\Entity\PetActivityLogTag;
use App\Exceptions\PSPNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PetActivityLogTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method PetActivityLogTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method PetActivityLogTag[]    findAll()
 * @method PetActivityLogTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class PetActivityLogTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetActivityLogTag::class);
    }

    /**
     * @param string[] $names
     * @return PetActivityLogTag[]
     * @deprecated
     */
    public function deprecatedFindByNames(array $names): array
    {
        return PetActivityLogTagRepository::findByNames($this->getEntityManager(), $names);
    }

    /**
     * @param string[] $names
     * @return PetActivityLogTag[]
     */
    public static function findByNames(EntityManagerInterface $em, array $names): array
    {
        $tags = [];

        foreach($names as $name)
            $tags[] = PetActivityLogTagRepository::findOneByName($em, $name);

        return $tags;
    }

    public static function findOneByName(EntityManagerInterface $em, string $name): PetActivityLogTag
    {
        $tag = $em->getRepository(PetActivityLogTag::class)->createQueryBuilder('t')
            ->where('t.title=:title')
            ->setParameter('title', $name)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, 'PetActivityLogTagRepository_FindOneByName_' . $name)
            ->getOneOrNullResult();

        if(!$tag)
            throw new PSPNotFoundException('Could not find tag with name "' . $name . '"!');

        return $tag;
    }
}
