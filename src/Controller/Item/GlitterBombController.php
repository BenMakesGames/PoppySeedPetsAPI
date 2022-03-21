<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\PetLocationEnum;
use App\Enum\StatusEffectEnum;
use App\Repository\PetRepository;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/glitterBomb")
 */
class GlitterBombController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/toss", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        PetRepository $petRepository, Squirrel3 $squirrel3, HotPotatoService $hotPotatoService,
        StatusEffectService $statusEffectService
    )
    {
        $this->validateInventory($inventory, 'glitterBomb/#/toss');

        $user = $this->getUser();

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $pets = $petRepository->findBy([
                'owner' => $user,
                'location' => PetLocationEnum::HOME
            ]);

            foreach($pets as $pet)
                $statusEffectService->applyStatusEffect($pet, StatusEffectEnum::GLITTER_BOMBED, 12 * 60);

            $em->remove($inventory);
            $em->flush();

            if(count($pets) === 0)
                return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you. (Your pets would have presumably also been affected, but they\'re not here, so...)', [ 'itemDeleted' => true ]);
            else if(count($pets) === 1)
                return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and ' . $pets[0]->getName() . '.', [ 'itemDeleted' => true ]);
            else
                return $responseService->itemActionSuccess('You get ready to toss the Glitter Bomb, but it explodes, getting glitter all over you, and, more importantly, your pets.', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }
}
