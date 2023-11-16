<?php

namespace App\Functions;

use App\Entity\PetActivityLogTag;
use App\Exceptions\PSPNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final class PetActivityLogTagHelpers
{
    /**
     * @param string[] $names
     * @return PetActivityLogTag[]
     */
    public static function findByNames(EntityManagerInterface $em, array $names): array
    {
        $tags = [];

        foreach($names as $name)
            $tags[] = PetActivityLogTagHelpers::findOneByName($em, $name);

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
            throw new \InvalidArgumentException('Could not find tag with name "' . $name . '"!');

        return $tag;
    }
}
