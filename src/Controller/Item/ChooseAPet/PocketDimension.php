<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/pocketDimension")
 */
class PocketDimension extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pocketDimension');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $merit = $em->getRepository(Merit::class)->findOneBy([ 'name' => MeritEnum::BIGGER_LUNCHBOX ]);

        if(!$merit)
            throw new \Exception("Ben forgot to put the Bigger Lunchbox merit in the database! Agk!");

        if($pet->hasMerit($merit->getName()))
            throw new PSPFormValidationException($pet->getName() . '\'s lunchbox is already bigger on the inside!');

        $pet->addMerit($merit);

        PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name%'s lunchbox got bigger!")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
            ->setChanges($petChanges->compare($pet))
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()}'s lunchbox got bigger... but only on the inside? \*shrugs\* Good enough.",
            [ 'itemDeleted' => true ]
        );
    }
}