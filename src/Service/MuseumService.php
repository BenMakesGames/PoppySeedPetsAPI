<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumService
{
    private EntityManagerInterface $em;
    private UserStatsService $userStatsRepository;
    private TransactionService $transactionService;

    public function __construct(
        EntityManagerInterface $em, UserStatsService $userStatsRepository,
        TransactionService $transactionService
    )
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->transactionService = $transactionService;
    }

    /**
     * @param string|number|Item $item
     */
    public function forceDonateItem(User $user, $item, ?string $comment, ?User $createdBy = null): MuseumItem
    {
        if(is_string($item))
            $item = ItemRepository::findOneByName($this->em, $item);
        else if(is_numeric($item))
            $item = ItemRepository::findOneById($this->em, $item);

        $museumItem = $this->em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user,
            'item' => $item
        ]);

        if($museumItem)
            return $museumItem;

        $museumItem = (new MuseumItem())
            ->setUser($user)
            ->setItem($item)
            ->setCreatedBy($createdBy)
        ;

        if($comment)
            $museumItem->setComments([ $comment ]);

        $this->em->persist($museumItem);

        $this->transactionService->getMuseumFavor($user, $item->getMuseumPoints(), 'Someone, or something, donated ' . $item->getNameWithArticle() . ' to the Museum on your behalf.');

        $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, 1);

        return $museumItem;
    }

    public function getGiftShopInventory(User $user): array
    {
        $inventory = [];

        /** @var ItemGroup[] $groups */
        $groups = $this->em->getRepository(ItemGroup::class)->findBy([
            'isGiftShop' => true
        ]);

        foreach($groups as $group)
        {
            $userDonated = $this->em->getRepository(MuseumItem::class)->count([
                'user' => $user,
                'item' => $group->getItems()->toArray()
            ]);

            $requiredToUnlock = ceil(count($group->getItems()) / 2);

            $forSale = [];

            if($userDonated >= $requiredToUnlock)
            {
                foreach($group->getItems() as $item)
                {
                    $forSale[] = [
                        'item' => [ 'name' => $item->getName(), 'image' => $item->getImage() ],
                        'cost' => $item->getMuseumPoints() * 10,
                    ];
                }
            }

            $inventory[] = [
                'category' => $group->getName(),
                'itemsDonated' => $userDonated,
                'requiredToUnlock' => $requiredToUnlock,
                'inventory' => $forSale
            ];

        }

        return $inventory;
    }
}
