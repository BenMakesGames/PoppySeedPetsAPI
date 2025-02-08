<?php
declare(strict_types=1);

namespace App\Controller\MonsterOfTheWeek;

use App\Entity\Inventory;
use App\Entity\MonsterOfTheWeek;
use App\Entity\MonsterOfTheWeekContribution;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\Clock;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/monsterOfTheWeek")]
class GetFoodController extends AbstractController
{
    #[Route("/{monsterId}/getFood", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFood(
        int $monsterId, ResponseService $responseService, EntityManagerInterface $em,
        Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $monster = $em->getRepository(MonsterOfTheWeek::class)->findOneBy([
            'id' => $monsterId
        ]);

        if($clock->now->setTime(0, 0, 0) < $monster->getStartDate() || $clock->now->setTime(0, 0, 0) > $monster->getEndDate())
            throw new PSPInvalidOperationException("It is not the time for this spirit! (Reload and try again?)");

        $inventoryAtHome = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $foods = [];

        foreach($inventoryAtHome as $i)
        {
            $points = MonsterOfTheWeekHelpers::getItemValue($monster->getMonster(), $i->getItem());

            if($points < 1) continue;

            $foods[] = [
                'id' => $i->getId(),
                'item' => [
                    'name' => $i->getItem()->getName(),
                    'image' => $i->getItem()->getImage(),
                ],
                'points' => $points
            ];
        }

        return $responseService->success($foods);
    }
}
