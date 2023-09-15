<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\GuildEnum;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class GivingTreeGatheringService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;

    public function __construct(
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService,
        PetExperienceService $petExperienceService, Squirrel3 $squirrel3
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
    }

    public function gatherFromGivingTree(Pet $pet): ?PetActivityLog
    {
        $givingTree = $this->em->getRepository(User::class)->findOneBy([ 'email' => 'giving-tree@poppyseedpets.com' ]);

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
            $givingTreeItems = $this->squirrel3->rngNextInt(5, 8);

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

            if($pet->isInGuild(GuildEnum::GIZUBIS_GARDEN, 1))
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(20, 30), PetActivityStatEnum::OTHER, null);

                $pet->getGuildMembership()->increaseReputation();

                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited The Giving Tree, and picked up several items that other players had discarded. In honor of Gizubi\'s Tree of Life, they also took a few minutes to water the Giving Tree.', 'icons/activity-logs/giving-tree')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Giving Tree', 'Guild' ]))
                ;
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(10, 20), PetActivityStatEnum::OTHER, null);

                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited The Giving Tree, and picked up several items that other players had discarded.', 'icons/activity-logs/giving-tree')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Giving Tree' ]))
                ;
            }
        }
    }

}
