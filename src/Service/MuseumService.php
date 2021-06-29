<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\ItemGroupRepository;
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
    private ItemGroupRepository $itemGroupRepository;

    public function __construct(
        EntityManagerInterface $em, ItemRepository $itemRepository, UserStatsRepository $userStatsRepository,
        MuseumItemRepository $museumItemRepository, ItemGroupRepository $itemGroupRepository
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->userStatsRepository = $userStatsRepository;
        $this->museumItemRepository = $museumItemRepository;
        $this->itemGroupRepository = $itemGroupRepository;
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

        $user->addMuseumPoints($item->getMuseumPoints());

        $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, 1);

        return $museumItem;
    }

    public function getGiftShopInventory(User $user)
    {
        $inventory = [];

        /** @var ItemGroup[] $groups */
        $groups = $this->itemGroupRepository->findBy([
            'isGiftShop' => true
        ]);

        foreach($groups as $group)
        {
            $userDonated = $this->museumItemRepository->count([
                'user' => $user,
                'item' => $group->getItems()
            ]);

            if($userDonated >= count($group->getItems()) / 2)
            {
                foreach($group->getItems() as $item)
                {
                    $inventory[] = [
                        'item' => [ 'name' => $item->getName(), 'image' => $item->getImage() ],
                        'cost' => $item->getMuseumPoints() * 10,
                    ];
                }
            }
        }

        return $inventory;
    }
}
