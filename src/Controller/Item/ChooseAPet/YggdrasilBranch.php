<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/yggdrasilBranch")
 */
class YggdrasilBranch extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em,
        IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'yggdrasilBranch');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $randomMerit = $rng->rngNextFromArray([
            MeritEnum::WONDROUS_STRENGTH,
            MeritEnum::WONDROUS_STAMINA,
            MeritEnum::WONDROUS_DEXTERITY,
            MeritEnum::WONDROUS_PERCEPTION,
            MeritEnum::WONDROUS_INTELLIGENCE,
        ]);

        $merit = $em->getRepository(Merit::class)->findOneBy([ 'name' => $randomMerit ]);

        if(!$merit)
            throw new \Exception("Merit not found: {$randomMerit}");

        if($pet->hasMerit($randomMerit))
        {
            $leaves = $rng->rngNextFromArray([
                'melts away',
                'evaporates',
                'dissipates',
                'vanishes'
            ]);

            $itemActionDescription = "ate the fruit of the Yggdrasil Branch, and their {$randomMerit} {$leaves}!";
            $pet->removeMerit($merit);
        }
        else
        {
            $pet->addMerit($merit);
            $itemActionDescription = "ate the fruit of the Yggdrasil Branch, and was blessed with {$randomMerit}!";
        }

        PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$itemActionDescription}")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
            ->setChanges($petChanges->compare($pet))
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()} {$itemActionDescription}!",
            [ 'itemDeleted' => true ]
        );
    }
}