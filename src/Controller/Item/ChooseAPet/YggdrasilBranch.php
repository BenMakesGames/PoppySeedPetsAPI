<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/item/yggdrasilBranch")
 */
class YggdrasilBranch extends ChooseAPetController
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
        MeritRepository $meritRepository,
        IRandom $rng
    )
    {
        $this->validateInventory($inventory, 'yggdrasilBranch/#');

        $pet = $this->getPet($request, $petRepository);

        $randomMerit = $rng->rngNextFromArray([
            MeritEnum::WONDROUS_STRENGTH,
            MeritEnum::WONDROUS_STAMINA,
            MeritEnum::WONDROUS_DEXTERITY,
            MeritEnum::WONDROUS_PERCEPTION,
            MeritEnum::WONDROUS_INTELLIGENCE,
        ]);

        $merit = $meritRepository->findOneByName($randomMerit);

        if($pet->hasMerit($randomMerit))
        {
            $leaves = $rng->rngNextFromArray([
                'melts away',
                'evaporates',
                'dissipates',
                'vanishes'
            ]);

            $itemActionDescription = 'ate the fruit of the Yggdrasil Branch, and their ' . $randomMerit . ' ' . $leaves . '!';
            $pet->removeMerit($merit);
        }
        else
        {
            $pet->addMerit($merit);
            $itemActionDescription = 'ate the fruit of the Yggdrasil Branch, and was blessed with ' . $randomMerit . '!';
        }

        $responseService->createActivityLog($pet, "%pet:{$pet->getId()}% $itemActionDescription", '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
            ->setViewed()
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()} {$itemActionDescription}!",
            [ 'itemDeleted' => true ]
        );
    }
}