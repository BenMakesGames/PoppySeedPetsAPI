<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\StatusEffectHelpers;
use App\Repository\PetRepository;
use App\Service\HotPotatoService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/glitterBomb")]
class GlitterBombController extends AbstractController
{
    #[Route("/{inventory}/toss", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        PetRepository $petRepository, IRandom $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'glitterBomb/#/toss');

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $pets = $petRepository->findBy([
                'owner' => $user,
                'location' => PetLocationEnum::HOME
            ]);

            foreach($pets as $pet)
                StatusEffectHelpers::applyStatusEffect($em, $pet, StatusEffectEnum::GLITTER_BOMBED, 12 * 60);

            $em->remove($inventory);
            $em->flush();

            if(count($pets) === 0)
                return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you. (Your pets would have presumably also been affected, but they\'re not here, so...)', [ 'itemDeleted' => true ]);
            else
            {
                $responseService->setReloadPets();

                if(count($pets) === 1)
                    return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and ' . $pets[0]->getName() . '.', [ 'itemDeleted' => true ]);
                else
                    return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and, more importantly, your pets.', [ 'itemDeleted' => true ]);
            }
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }
}
