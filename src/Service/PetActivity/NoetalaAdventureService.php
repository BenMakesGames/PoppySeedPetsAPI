<?php

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class NoetalaAdventureService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private IRandom $rng;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng
    )
    {
        $this->em = $em;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->rng = $rng;
    }

    public function fightNoetalasWing(User $user): bool
    {
        $petsAtHome = $this->em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            $this->responseService->addFlashMessage('Entering such a terrifying portal on your own would be... _unwise_. (You need some pets at home to help!)');
            return false;
        }

        $petChanges = [];
        $totalSkill = 0;

        foreach($petsAtHome as $pet)
        {
            $petWithSkills = $pet->getComputedSkills();

            $totalSkill += $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getDexterity()->getTotal();
            $petChanges[$pet->getId()] = new PetChanges($pet);
        }

        $activityNames = array_map(fn(Pet $pet) => ActivityHelpers::PetName($pet), $petsAtHome);
        $names = array_map(fn(Pet $pet) => $pet->getName(), $petsAtHome);

        $message = ActivityHelpers::UserName($user, true) . ' stepped into the portal with ' . ArrayFunctions::list_nice($activityNames) . ', and found yourselves in a black space whose edges could not be seen. (Some dark pocket of the Umbra, no doubt.) A huge, bat-like wing emerged from the shadows, radiating a terrifying aura. ';
        $success = false;

        if($totalSkill < 8)
        {
            $message .= 'You all fled back through the portal, shaken. Whatever creature that wing belonged to, it must have been at least 10 times stronger than all of you combined!';
        }
        else if($totalSkill < 30)
        {
            $message .= 'You all retreated back through the portal. Whatever creature that wing belonged to, you all together could not have totalled more than one-quarter its strength!';
        }
        else if($totalSkill < 50)
        {
            $message .= 'You all retreated back through the portal. Whatever creature that wing belonged to, you all together were only about half its strength!';
        }
        else if($totalSkill < 70)
        {
            $message .= 'You started to fight, but the wing\'s slices were powerful and wild. Even all together, you weren\'t a match for it...';
        }
        else if($totalSkill < 80)
        {
            $message .= 'You fought for a while against the wing\'s powerful, wild slices, but even working together, you were eventually forced to retreat. With just a little more fighting strength, though, you feel you\'d have a chance!';
        }
        else
        {
            $message .= 'You fought for a while against the wing\'s powerful, wild slices, slowly but surely wearing it down. ';

            $finalBlow = ArrayFunctions::pick_one_weighted($petsAtHome, fn(Pet $pet) => $pet->getComputedSkills()->getBrawl()->getTotal() + $pet->getComputedSkills()->getStrength()->getTotal() + $pet->getComputedSkills()->getStamina()->getTotal() + $pet->getComputedSkills()->getDexterity()->getTotal());

            $message .= 'At last, ' . ActivityHelpers::PetName($finalBlow) . ' saw a window of opportunity, and attacked, landing the critical and final blow! Near-deafening screeches echoed in the darkness, and the wing retreated. You escaped back through the portal, scratched, bruised, and exhausted, but victorious! (And with a little loot, of course! (I mean, this _is_ a video game, after all!))';

            $rewards = [ 'Bat Hat', 'Perse Batling', 'Pallid Batling' ];

            $this->rng->rngNextShuffle($rewards);

            $i = 0;

            foreach($petsAtHome as $pet)
            {
                $pet->increaseEsteem(12)->increaseFood(-12);

                $log = PetActivityLogFactory::createReadLog($this->em, $pet, $message);

                StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::BITTEN_BY_A_VAMPIRE, 1);

                $this->inventoryService->petCollectsItem($rewards[$i], $pet, ArrayFunctions::list_nice($names) . ' defeated Noetala\'s Wing, and received this.', $log);
                $this->inventoryService->petCollectsItem('Quintessence', $pet, ArrayFunctions::list_nice($names) . ' defeated Noetala\'s Wing, and received this.', $log);

                $log
                    ->setChanges($petChanges[$pet->getId()]->compare($pet))
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
                ;

                $i = ($i + 1) % count($rewards);
            }

            UserStatsRepository::incrementStat($this->em, $user, 'Defeated Noetala\'s Wing');

            $success = true;
        }

        $this->responseService->addFlashMessage($message);

        return $success;
    }
}