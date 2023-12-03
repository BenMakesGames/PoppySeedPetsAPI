<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\UserStatEnum;
use App\Model\SummoningScrollMonster;
use App\Service\IRandom;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/item/summoningScroll')]
class SummoningController extends AbstractController
{
    #[Route("/{inventory}/unfriendly", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HouseMonsterService $houseMonsterService, IRandom $squirrel3, UserStatsService $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'summoningScroll/#/unfriendly');

        $em->remove($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('Summoning something so terrifying on your own would be... _unwise_. (You need some pets at home to help!)');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateDragon(),
            SummoningScrollMonster::CreateBalrog(),
            SummoningScrollMonster::CreateBasabasa(),
            SummoningScrollMonster::CreateIfrit(),
            SummoningScrollMonster::CreateCherufe(),
        ]);

        $result = $houseMonsterService->doFight('You read the scroll', $petsAtHome, $monster);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/unfriendly2", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function summonSomethingFromDeepSpace(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HouseMonsterService $houseMonsterService, IRandom $squirrel3
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'summoningScroll/#/unfriendly2');

        $em->remove($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('Summoning something so terrifying on your own would be... _unwise_. (You need some pets at home to help!)');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateCrystallineEntity(),
            SummoningScrollMonster::CreateBivusRelease(),
            SummoningScrollMonster::CreateSpaceJelly(),
        ]);

        $result = $houseMonsterService->doFight('You cast your voice into the mirror', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }
}
