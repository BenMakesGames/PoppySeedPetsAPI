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
class ContributeController extends AbstractController
{
    #[Route("/{monsterId}/contribute", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeContribution(
        int $monsterId, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $monster = $em->getRepository(MonsterOfTheWeek::class)->findOneBy([
            'id' => $monsterId
        ]);

        if($clock->now < $monster->getStartDate() || $clock->now > $monster->getEndDate())
            throw new PSPInvalidOperationException("It is not the time for this spirit! (Reload and try again?)");

        $itemIds = $request->get('items', []);

        if(!is_array($itemIds) || count($itemIds) == 0)
            throw new PSPInvalidOperationException('You must select at least one item!');

        if(count($itemIds) > 100)
            throw new PSPInvalidOperationException('Please contribute only up to 100 items at a time; thanks!');

        $items = $em->getRepository(Inventory::class)->findBy([
            'id' => $itemIds,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(count($items) < count($itemIds))
            throw new PSPInvalidOperationException('Could not find one or more of the selected items. (Reload and try again?)');

        $totalPoints = 0;

        foreach($items as $item)
        {
            $points = MonsterOfTheWeekHelpers::getItemValue($monster->getMonster(), $item->getItem());

            $em->remove($item);

            if($points < 1)
                throw new PSPInvalidOperationException('The spirit is not interested in one or more of the selected items! (Reload and try again?)');

            $totalPoints += $points;
        }

        $contribution = $em->getRepository(MonsterOfTheWeekContribution::class)->findOneBy([
            'monsterOfTheWeek' => $monster,
            'user' => $user
        ]);

        if($contribution === null)
        {
            $contribution = (new MonsterOfTheWeekContribution())
                ->setMonsterOfTheWeek($monster)
                ->setUser($user);

            $em->persist($contribution);
        }

        $contribution->addPoints($totalPoints);

        $em->flush();

        return $responseService->success($contribution->getPoints());
    }
}
