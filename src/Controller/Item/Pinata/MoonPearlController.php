<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/moonPearl")]
class MoonPearlController extends AbstractController
{
    #[Route("/{inventory}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function smash(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, PetExperienceService $petExperienceService,
        IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'moonPearl/#/smash');

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, 'The remains of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);
        $inventoryService->receiveItem('Moon Dust', $user, $user, 'The contents of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);
        $inventoryService->receiveItem('Moon Dust', $user, $user, 'The contents of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);

        $reloadPets = false;

        if($squirrel3->rngNextInt(1, 20) === 1 && $user->getGreenhouse() && $user->getGreenhouse()->getMaxDarkPlants() > 0)
        {
            $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, some Silica Grounds, AND WHAT\'S THIS? A Moondial Blueprint?!';

            $inventoryService->receiveItem('Moondial Blueprint', $user, $user, 'Found inside a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);
        }
        else
        {
            $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds.';

            /** @var Pet[] $helper */
            $availableHelpers = $em->getRepository(Pet::class)->findBy([
                'owner' => $user->getId(),
                'location' => PetLocationEnum::HOME
            ]);

            if(count($availableHelpers) > 0)
            {
                /** @var Pet $helper */
                $helper = $squirrel3->rngNextFromArray($availableHelpers);

                $helperWithSkills = $helper->getComputedSkills();
                $skill = 20 + $helperWithSkills->getArcana()->getTotal() + $helperWithSkills->getIntelligence()->getTotal() + $helperWithSkills->getDexterity()->getTotal();

                if($squirrel3->rngNextInt(1, $skill) >= 16)
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::UserName($user, true) . ' shattered a moon pearl; ' . ActivityHelpers::PetName($helper) . ' gathered up some of its Quintessence before it could evaporate away!');

                    $inventoryService->petCollectsItem('Quintessence', $helper, $helper->getName() . ' caught this as it escaped from a shattered Moon Pearl.', $activityLog);

                    $petExperienceService->gainExp($helper, 2, [ PetSkillEnum::ARCANA ], $activityLog);

                    $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds, and ' . $helper->getName() . ' gathers up the Quintessence before it evaporates away.';

                    if($location !== LocationEnum::HOME)
                        $message .= ' (' . $helper->getName() . ' placed the items they got in the house... that\'s just where pets put the stuff they get, you know!)';

                    $reloadPets = true;
                }
            }
        }

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
