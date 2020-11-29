<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/moonPearl")
 */
class MoonPearlController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/smash", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function smash(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, PetRepository $petRepository, PetExperienceService $petExperienceService
    )
    {
        $this->validateInventory($inventory, 'moonPearl/#/smash');

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, 'The remains of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);
        $inventoryService->receiveItem('Moon Dust', $user, $user, 'The contents of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);
        $inventoryService->receiveItem('Moon Dust', $user, $user, 'The contents of a Moon Pearl which was shattered by ' . $user->getName() . '.', $location);

        /** @var Pet $helper */
        $helper = ArrayFunctions::pick_one($petRepository->findBy([
            'owner' => $user->getId(),
            'inDaycare' => false
        ]));

        $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds.';
        $reloadPets = false;

        if($helper)
        {
            $helperWithSkills = $helper->getComputedSkills();
            $skill = 20 + $helperWithSkills->getUmbra()->getTotal() + $helperWithSkills->getIntelligence()->getTotal() + $helperWithSkills->getDexterity()->getTotal();

            if(mt_rand(1, $skill) >= 16)
            {
                $petExperienceService->gainExp($helper, 2, [ PetSkillEnum::UMBRA ]);

                $inventoryService->receiveItem('Quintessence', $user, $user, $helper->getName() . ' caught this as it escaped from a shattered Moon Pearl.', $location);

                $message = 'You shatter the Moon Pearl, yielding a couple lumps of Moon Dust, and some Silica Grounds, and ' . $helper->getName() . ' gathers up the Quintessence before it evaporates away.';
                $reloadPets = true;
            }
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }
}
