<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/smilingWand")]
class SmilingWandController extends AbstractController
{
    #[Route("/{inventory}/use", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function waveSmilingWand(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'smilingWand');

        $expressions = trim($request->request->getString('expressions', ''));

        if(!self::validExpressions($expressions))
            throw new PSPFormValidationException('You must select three different expressions.');

        $petId = $request->request->getInt('pet', 0);

        /** @var Pet $pet */
        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        $pet->setAffectionExpressions($expressions);

        $em->remove($inventory);
        $em->flush();

        PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::UserName($user, true) . ' waved a Smiling Wand over ' . ActivityHelpers::PetName($pet) . ', changing how they express themselves when pet!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    private static function validExpressions(string $expressions): bool
    {
        if(strlen($expressions) !== 3)
            return false;

        $seenExpressions = [];

        for($i = 0; $i < 3; $i++)
        {
            $char = $expressions[$i];

            if(in_array($char, $seenExpressions))
                return false;

            if($char < 'A' || $char > 'Z')
                return false;

            $seenExpressions[] = $char;
        }

        return true;
    }
}
