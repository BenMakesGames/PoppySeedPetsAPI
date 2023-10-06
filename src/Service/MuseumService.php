<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumService
{
    private EntityManagerInterface $em;
    private ItemRepository $itemRepository;
    private MuseumItemRepository $museumItemRepository;
    private TransactionService $transactionService;

    public function __construct(
        EntityManagerInterface $em, ItemRepository $itemRepository,
        MuseumItemRepository $museumItemRepository, TransactionService $transactionService
    )
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->museumItemRepository = $museumItemRepository;
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
            ->setCreatedBy($createdBy)
        ;

        if($comment)
            $museumItem->setComments([ $comment ]);

        $this->em->persist($museumItem);

        $this->transactionService->getMuseumFavor($user, $item->getMuseumPoints(), 'Someone, or something, donated ' . $item->getNameWithArticle() . ' to the Museum on your behalf.');

        UserStatsRepository::incrementStat($this->em, $user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, 1);

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
            $userDonated = $this->museumItemRepository->count([
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
