<?php
namespace App\Service;

use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumService
{
    private $em;
    private $itemRepository;
    private $userStatsRepository;

    public function __construct(
        EntityManagerInterface $em, ItemRepository $itemRepository, UserStatsRepository $userStatsRepository
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function forceDonateItem(User $user, string $item, ?string $comment)
    {
        $museumItem = (new MuseumItem())
            ->setUser($user)
            ->setItem($this->itemRepository->findOneByName($item))
            ->setCreatedBy(null)
        ;

        if($comment)
            $museumItem->setComments([ $comment ]);

        $this->em->persist($museumItem);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, 1);
    }
}
