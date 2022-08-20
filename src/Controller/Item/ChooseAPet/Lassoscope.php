<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/item/lassoscope")
 */
class Lassoscope extends ChooseAPetController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        PetRepository $petRepository,
        EntityManagerInterface $em,
        UserQuestRepository $questRepository,
        PetActivityLogTagRepository $petActivityLogTagRepository,
        IRandom $rng
    )
    {
        $this->validateInventory($inventory, 'lassoscope/#');

        $pet = $this->getPet($request, $petRepository);

        $now = (new \DateTimeImmutable())->format('Y-m-d');
        $lassoscope = $questRepository->findOrCreate($this->getUser(), 'Used a Lassoscope', (new \DateTimeImmutable())->modify(\DateInterval::createFromDateString('-1 day'))->format('Y-m-d'));

        if($lassoscope->getValue() === $now)
        {
            return $responseService->itemActionSuccess('You\'re unlikely to see anything different on the same day. (You gotta\' wait until tomorrow to use a Lassoscope.)');
        }

        if($pet->getSkills()->getScience() >= 20 && $pet->getSkills()->getNature() >= 20)
        {
            return $responseService->itemActionSuccess("{$pet->getName()} has basically seen it all; there's nothing more for them to learn from using a Lassoscope!");
        }

        $lassoscope->setValue($now);

        if($rng->rngNextBool())
        {
            if($pet->getSkills()->getScience() < $pet->getSkills()->getNature())
                $skill = PetSkillEnum::SCIENCE;
            else if($pet->getSkills()->getNature() < $pet->getSkills()->getScience())
                $skill = PetSkillEnum::NATURE;
            else
                $skill = $rng->rngNextBool() ? PetSkillEnum::NATURE : PetSkillEnum::SCIENCE;

            $pet->getSkills()->increaseStat($skill);

            $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% observed from lions through a Lassoscope, and learned a lot... so much, they leveled up! +1 ' . ucfirst($skill) . '!', '')
                ->addTag($petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
            ;

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess(
                "{$pet->getName()} observed from lions for a while - safely! from a distance! - and learned a lot... so much, they leveled up! +1 " . ucfirst($skill) . "!\n\nBecause this is a video game, the Lassoscope was consumed in the process. If you like, picture it flying away, to start a new chapter in its own life.\n\nNature is truly beautiful.",
                [ 'itemDeleted' => true ]
            );
        }
        else
        {
            $em->flush();

            return $responseService->itemActionSuccess("{$pet->getName()} looks around for a while, but doesn't spot anything. Maybe tomorrow will be more fruitful.");
        }
    }
}