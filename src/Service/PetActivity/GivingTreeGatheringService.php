<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GivingTreeGatheringService
{
    private $userRepository;
    private $inventoryService;
    private $responseService;
    private $em;

    public function __construct(
        UserRepository $userRepository, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $this->responseService = $responseService;
        $this->userRepository = $userRepository;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
    }

    public function gatherFromGivingTree(Pet $pet): ?PetActivityLog
    {
        $givingTree = $this->userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new \Exception('The "Giving Tree" NPC does not exist in the database!');

        $items = $this->inventoryService->countTotalInventory($givingTree, LocationEnum::HOME);

        // just to make suuuuuuuuuuuuuuuuuuper sure that there's enough for every pet that might be doing this...
        if($items < 100)
        {
            return null;
        }
        else
        {
            $givingTreeItems = mt_rand(5, 8);

            $this->em->getConnection()->executeQuery(
                '
                    UPDATE inventory
                    SET
                        owner_id=:newOwner,
                        modified_on=NOW()
                    WHERE owner_id=:givingTree
                    LIMIT ' . $givingTreeItems . '
                ',
                [
                    'newOwner' => $pet->getOwner()->getId(),
                    'givingTree' => $givingTree->getId()
                ]
            );

            $pet->spendTime(mt_rand(10, 20));

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' visited The Giving Tree, and picked up several items that other players had discarded.', '');
        }
    }

}