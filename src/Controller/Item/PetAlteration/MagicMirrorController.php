<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magicMirror")
 */
class MagicMirrorController extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useMagicMirror(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicMirror');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $merit = $meritRepository->findOneByName(MeritEnum::MIRRORED);

        if(!$merit)
            throw new \Exception('The ' . MeritEnum::MIRRORED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        if($pet->hasMerit(MeritEnum::MIRRORED))
        {
            $pet->removeMerit($merit);
            $messageExtra = $pet->getName() . ' is no longer Mirrored.';
        }
        else
        {
            $pet->addMerit($merit);
            $messageExtra = $pet->getName() . ' has become Mirrored!';
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . ' stared so hard at the mirror, it shattered! ' . $messageExtra);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
