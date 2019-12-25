<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetGroupTypeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetGroupService
{
    private $em;
    private $petRepository;
    private $responseService;
    private $petExperienceService;
    private $inventoryService;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService,
        PetExperienceService $petExperienceService, InventoryService $inventoryService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
    }

    public function doGroupActivity(Pet $instigatingPet, PetGroup $group)
    {
        foreach($group->getMembers() as $member)
        {
            $time = ($member->getId() === $instigatingPet->getId()) ? mt_rand(45, 60) : 5;

            $this->petExperienceService->spendTime($member, $time, PetActivityStatEnum::HANG_OUT, true);
        }

        switch($group->getType())
        {
            case PetGroupTypeEnum::BAND:
                $this->doBandActivity($instigatingPet, $group);
                break;

            default:
                throw new \Exception('Unhandled group type "' . $group->getType() . '"');
        }
    }

    public function createGroup(Pet $pet): ?PetGroup
    {
        $availableFriends = $this->petRepository->findFriendsWithFewGroups($pet);

        if(count($availableFriends) < 2)
            return null;

        // @TODO: when we have more than one group type, we'll have to pick one here
        $type = PetGroupTypeEnum::BAND;

        $group = (new PetGroup())
            ->setType($type)
        ;

        $this->em->persist($group);

        $pet->addGroup($group);

        switch($type)
        {
            case PetGroupTypeEnum::BAND:
                usort($availableFriends, function (Pet $a, Pet $b) {
                    return $b->getMusic() <=> $a->getMusic();
                });
                break;

            default:
                shuffle($availableFriends);
        }

        $friendNames = [
            $availableFriends[0]->getName(),
            $availableFriends[1]->getName(),
        ];

        $availableFriends[0]->addGroup($group);
        $availableFriends[1]->addGroup($group);

        $this->petExperienceService->spendTime($availableFriends[0], 5, PetActivityStatEnum::HANG_OUT, true);
        $this->petExperienceService->spendTime($availableFriends[1], 5, PetActivityStatEnum::HANG_OUT, true);

        if(count($availableFriends) >= 3 && mt_rand(1, 2) === 1)
        {
            $availableFriends[2]->addGroup($group);
            $this->petExperienceService->spendTime($availableFriends[2], 5, PetActivityStatEnum::HANG_OUT, true);
            $friendNames[] = $availableFriends[2]->getName();
        }

        if(count($availableFriends) >= 4 && mt_rand(1, 2) === 1)
        {
            $availableFriends[3]->addGroup($group);
            $this->petExperienceService->spendTime($availableFriends[3], 5, PetActivityStatEnum::HANG_OUT, true);
            $friendNames[] = $availableFriends[3]->getName();
        }

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HANG_OUT, true);

        $this->responseService->createActivityLog($pet, $pet->getName() . ' started a new band with ' . ArrayFunctions::list_nice($friendNames) . '.', 'items/music/note');

        return $group;
    }

    private function doBandActivity(Pet $instigatingPet, PetGroup $group)
    {
        $skill = 0;
        $progress = 0;

        foreach($group->getMembers() as $pet)
        {
            $roll = mt_rand(1, 10 + $pet->getMusic());

            $skill += $roll;
            $progress += mt_rand(1, mt_rand(2, mt_rand(4, 12)));

            $this->petExperienceService->gainExp($pet, max(1, floor($roll / 5)), [ PetSkillEnum::MUSIC ]);
        }

        $group
            ->increaseProgress($progress)
            ->increaseSkillRollTotal($skill)
        ;

        if($group->getProgress() >= 100)
        {
            $group->clearProgress();
        }
        else
        {
            if($group->getSkillRollTotal() < 60)
                $item = 'Single';
            else if($group->getSkillRollTotal() < 100)
                $item = 'EP';
            else //if($group->getSkillRollTotal() < 150)
                $item = 'LP';

            // @TODO:
            /*
            else //if($group->getSkillRollTotal() < 200)
                $item = 'Album';
            */

            foreach($group->getMembers() as $member)
            {
                $this->inventoryService->receiveItem($item, $member->getOwner(), $member->getOwner(), $member->getName() . '\'s band made this!', LocationEnum::HOME);

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($member->getName() . '\'s band made a new ' . $item . '!')
                    ->setIcon('items/music/note')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ;

                $this->em->persist($activityLog);

                if($member->getId() === $instigatingPet->getId())
                    $this->responseService->addActivityLog($activityLog);
            }
        }
    }
}