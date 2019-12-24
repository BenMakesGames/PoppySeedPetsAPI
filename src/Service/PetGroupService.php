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

    public function createGroup(Pet $pet): ?PetGroup
    {
        $availableFriends = $this->petRepository->findFriendsWithFewGroups($pet);

        if(count($availableFriends) === 0)
            return null;

        // @TODO: when we have more than one group type, we'll have to pick one here
        $type = PetGroupTypeEnum::BAND;

        $group = (new PetGroup())
            ->setType($type)
        ;

        $this->em->persist($group);

        $pet->addGroup($group);

        shuffle($availableFriends);

        $availableFriends[0]->addGroup($group);

        if(count($availableFriends) >= 2)
            $availableFriends[1]->addGroup($group);

        return $group;
    }
}