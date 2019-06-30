<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Entity\UserFriend;
use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    /**
     * @var User|null
     */
    private $user;

    public function __construct(UserRepository $userRepository)
    {
        $this->filterer = new Filterer(
            $userRepository, 'u',
            self::PAGE_SIZE,
            [
                'lastActivity' => [ 'u.lastActivity', 'desc' ], // first one is the default
                'registeredOn' => [ 'u.registeredOn', 'asc' ],
                'name' => [ 'u.name', 'asc' ],
                'id' => [ 'u.id', 'asc' ],
            ],
            [
                'name' => [ $this, 'filterName' ],
                'friendedBy' => [ $this, 'filterFriendedBy' ],
            ]
        );
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('u.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterFriendedBy(QueryBuilder $qb, $value)
    {
        if($this->user && ($value === $this->user->getId() || $this->user->hasRole('ROLE_ADMIN')))
        {
            if(!in_array('f', $qb->getAllAliases()))
                $qb->leftJoin('u.friendsOf', 'f');

            $qb
                ->andWhere('f.user = :friendedBy')
                ->setParameter('friendedBy', $value)
            ;
        }
    }
}