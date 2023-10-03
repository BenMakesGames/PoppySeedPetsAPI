<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/scrollOfIllusions")
 */
class ApplyIllusionController extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buyIllusion(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfIllusions');

        $petId = $request->query->getInt('petId');
        $illusionId = $request->query->getInt('illusionId');

        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        if(!$pet->getTool())
            throw new PSPInvalidOperationException('This pet does not have a tool equipped.');

        // verify that the user has donated the illusionId in question
        $donation = $em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user,
            'item' => $illusionId,
        ]);

        if(!$donation)
            throw new PSPNotFoundException('You have not donated one of those to the Museum...');

        $pet->getTool()->setIllusion($donation->getItem());

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();
        $responseService->setReloadPets();

        return $responseService->success();
    }
}