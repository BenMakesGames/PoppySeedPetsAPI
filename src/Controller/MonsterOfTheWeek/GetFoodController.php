<?php
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

        if($clock->now < $monster->getStartDate() || $clock->now > $monster->getEndDate())
            throw new PSPInvalidOperationException("It is not the time for this monster! (Reload and try again?)");

        $items = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $foods = [];

        foreach($items as $item)
        {
            $points = MonsterOfTheWeekHelpers::getInventoryValue($monster->getMonster(), $item);

            if($points < 1) continue;

            $foods[] = [
                'id' => $item->getId(),
                'item' => [
                    'name' => $item->getItem()->getName(),
                    'image' => $item->getItem()->getImage(),
                ],
                'points' => $points
            ];
        }

        return $responseService->success($foods);
    }
}
