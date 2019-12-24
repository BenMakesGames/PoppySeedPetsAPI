<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetGroup;
use App\Enum\PetGroupTypeEnum;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetGroupService
{
    private $em;
    private $petRepository;

    public function __construct(EntityManagerInterface $em, PetRepository $petRepository)
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
    }

    public function doGroupActivity(PetGroup $group)
    {
        switch($group->getType())
        {
            case PetGroupTypeEnum::BAND:
                $this->doBandActivity($group);
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

        $availableFriends[0]->addGroup($group);
        $availableFriends[1]->addGroup($group);

        if(count($availableFriends) >= 3 && mt_rand(1, 2) === 1)
            $availableFriends[2]->addGroup($group);

        if(count($availableFriends) >= 4 && mt_rand(1, 2) === 1)
            $availableFriends[3]->addGroup($group);

        return $group;
    }

    private function doBandActivity(PetGroup $group)
    {
        $skill = 0;
        $progress = 0;

        foreach($group->getMembers() as $pet)
        {
            $skill += mt_rand(1, 10 + $pet->getMusic());
            $progress += mt_rand(1, mt_rand(2, mt_rand(4, 12)));
        }


        $group
            ->increaseProgress($progress)
            ->increaseSkillRollTotal($skill)
        ;

        if($group->getProgress() >= 100)
        {

        }
    }
}