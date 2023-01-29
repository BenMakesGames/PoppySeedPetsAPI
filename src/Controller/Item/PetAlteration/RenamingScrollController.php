<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Functions\ProfanityFilterFunctions;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/renamingScroll")
 */
class RenamingScrollController extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readRenamingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'renamingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new UnprocessableEntityHttpException('This pet is Affectionless. It\'s not interested in taking on a new name.');

        $petName = ProfanityFilterFunctions::filter(trim($request->request->get('name', '')));

        if($petName === $pet->getName())
            throw new UnprocessableEntityHttpException('That\'s the pet\'s current name! What a waste of the scroll that would be...');

        if(\mb_strlen($petName) < 1 || \mb_strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        // let's not worry about this for now... it's a suboptimal solution
        /*
        if(!StringFunctions::isISO88591($petName))
            throw new UnprocessableEntityHttpException('Your pet\'s name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');
        */

        $responseService->createActivityLog($pet, "{$pet->getName()} has been renamed to {$petName}!", '');

        $em->remove($inventory);

        $pet->setName($petName);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
