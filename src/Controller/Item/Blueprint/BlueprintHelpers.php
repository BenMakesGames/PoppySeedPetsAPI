<?php
declare(strict_types=1);

namespace App\Controller\Item\Blueprint;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Greenhouse;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BlueprintHelpers
{
    public static function getPet(EntityManagerInterface $em, User $user, Request $request): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }

    public static function rewardHelper(
        PetExperienceService $petExperienceService, ResponseService $responseService,
        EntityManagerInterface $em, Pet $pet, ?string $skill, string $flashMessage, string $logMessage
    )
    {
        $squirrel3 = new Squirrel3();
        $changes = new PetChanges($pet);

        if($skill && $pet->getSkills()->getStat($skill) >= 20)
            $skill = null;

        $pet
            ->increaseLove($squirrel3->rngNextInt(3, 6))
            ->increaseEsteem($squirrel3->rngNextInt(2, 4))
        ;

        $petExperienceService->gainAffection($pet, 10);

        $responseService->addFlashMessage($flashMessage);

        $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, $logMessage)
            ->setIcon('ui/affection')
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        if($skill)
        {
            $pet->getSkills()->increaseStat($skill);

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
                ->setEntry($activityLog->getEntry() . ' +1 ' . ucfirst($skill) . '!')
                ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Level-up' ]));
        }
    }
}
