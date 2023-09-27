<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Model\SummoningScrollMonster;
use App\Repository\PetRepository;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/evilBeetle")
 */
class EvilSentientBeetleController extends AbstractController
{
    /**
     * @Route("/{inventory}/defeat", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        EntityManagerInterface $em, HouseMonsterService $houseMonsterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'evilBeetle/#/defeat');

        $em->remove($inventory);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('You have no pets at home! You can\'t defeat evil all on your own!');
        }

        $monster = SummoningScrollMonster::CreateDiscipleOfHunCame();

        $result = $houseMonsterService->doFight('You challenge the beetle', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }
}
