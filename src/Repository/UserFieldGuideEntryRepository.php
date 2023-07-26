<?php

namespace App\Repository;

use App\Entity\FieldGuideEntry;
use App\Entity\User;
use App\Entity\UserFieldGuideEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserFieldGuideEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFieldGuideEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFieldGuideEntry[]    findAll()
 * @method UserFieldGuideEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFieldGuideEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFieldGuideEntry::class);
    }

    private $userEntryPerRequestCache = [];

    public function findOrCreate(User $user, FieldGuideEntry $entry, string $comment): FindOrCreateResponse
    {
        $cacheKey = $user->getId() . '-' . $entry->getId();
        $created = false;

        if(array_key_exists($cacheKey, $this->userEntryPerRequestCache))
        {
            $record = $this->userEntryPerRequestCache[$cacheKey];
        }
        else
        {
            $record = $this->findOneBy([
                'user' => $user,
                'entry' => $entry,
            ]);

            if(!$record)
            {
                $record = $this->create($user, $entry, $comment);
                $created = true;
            }

            $this->userEntryPerRequestCache[$cacheKey] = $record;
        }

        return new FindOrCreateResponse($record, $created);
    }

    private function create(User $user, FieldGuideEntry $entry, string $comment): UserFieldGuideEntry
    {
        $record = (new UserFieldGuideEntry())
            ->setUser($user)
            ->setEntry($entry)
            ->setComment($comment)
        ;

        $this->getEntityManager()->persist($record);

        return $record;
    }

    public function doesExist(User $user, FieldGuideEntry $entry)
    {
        $cacheKey = $user->getId() . '-' . $entry->getId();

        if(!array_key_exists($cacheKey, $this->userEntryPerRequestCache))
        {
            $record = $this->findOneBy([
                'user' => $user,
                'entry' => $entry,
            ]);

            $this->userEntryPerRequestCache[$cacheKey] = $record;
        }

        return $this->userEntryPerRequestCache[$cacheKey] !== null;
    }
}

class FindOrCreateResponse
{
    public bool $wasCreated;
    public UserFieldGuideEntry $entry;

    public function __construct(UserFieldGuideEntry $entry, bool $wasCreated)
    {
        $this->entry = $entry;
        $this->wasCreated = $wasCreated;
    }
}