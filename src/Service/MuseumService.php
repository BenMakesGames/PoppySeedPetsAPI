<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumService
{
    private $em;
    private $itemRepository;
    private $userStatsRepository;
    private $museumItemRepository;

    public function __construct(
        EntityManagerInterface $em, ItemRepository $itemRepository, UserStatsRepository $userStatsRepository,
        MuseumItemRepository $museumItemRepository
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userStatsRepository = $userStatsRepository;
        $this->museumItemRepository = $museumItemRepository;
    }

    /**
     * @param string|number|Item $item
     */
    public function forceDonateItem(User $user, $item, ?string $comment): MuseumItem
    {
        if(is_string($item))
            $item = $this->itemRepository->findOneByName($item);
        else if(is_numeric($item))
            $item = $this->itemRepository->find($item);

        $museumItem = $this->museumItemRepository->findOneBy([
            'user' => $user,
            'item' => $item
        ]);

        if($museumItem)
            return $museumItem;

        $museumItem = (new MuseumItem())
            ->setUser($user)
            ->setItem($item)
            ->setCreatedBy(null)
        ;

        if($comment)
            $museumItem->setComments([ $comment ]);

        $this->em->persist($museumItem);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, 1);

        return $museumItem;
    }
}
