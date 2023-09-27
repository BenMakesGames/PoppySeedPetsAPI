<?php
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
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/moonPearl")
 */
class MoonPearlController extends AbstractController
{
    /**
     * @Route("/{inventory}/smash", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function smash(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, PetRepository $petRepository, PetExperienceService $petExperienceService,
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

        /** @var Pet $helper */
        $helper = $squirrel3->rngNextFromArray($petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]));

        $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds.';
        $reloadPets = false;

        if($helper)
        {
            $helperWithSkills = $helper->getComputedSkills();
            $skill = 20 + $helperWithSkills->getArcana()->getTotal() + $helperWithSkills->getIntelligence()->getTotal() + $helperWithSkills->getDexterity()->getTotal();

            if($squirrel3->rngNextInt(1, $skill) >= 16)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::UserName($user, true) . ' shattered a moon pearl; ' . ActivityHelpers::PetName($helper) . ' gathered up some of its Quintessence before it could evaporate away!');

                $inventoryService->petCollectsItem('Quintessence', $helper, $helper->getName() . ' caught this as it escaped from a shattered Moon Pearl.', $activityLog);

                $petExperienceService->gainExp($helper, 2, [ PetSkillEnum::ARCANA ], $activityLog);

                $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds, and ' . $helper->getName() . ' gathers up the Quintessence before it evaporates away.';

                if($location !== LocationEnum::HOME)
                    $message .= ' (' . $helper->getName() . ' placed the items they got in the house... that\'s just where pets that items get go!)';

                $reloadPets = true;
            }
        }

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
