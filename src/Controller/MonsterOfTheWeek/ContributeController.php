<?php
namespace App\Controller\MonsterOfTheWeek;

use App\Entity\Inventory;
use App\Entity\MonsterOfTheWeek;
use App\Entity\MonsterOfTheWeekContribution;
use App\Entity\User;
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
    #[Route("/{monsterId}/contribute", methods: ["GET"])]
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
            throw new PSPInvalidOperationException("It is not the time for this monster! (Reload and try again?)");

        $itemIds = $request->get('items', []);

        if(!is_array($itemIds) || count($itemIds) == 0)
            throw new PSPInvalidOperationException('You must select at least one item!');

        if(count($itemIds) > 100)
            throw new PSPInvalidOperationException('Please contribute only up to 100 items at a time; thanks!');

        $items = $em->getRepository(Inventory::class)->findBy([
            'id' => $itemIds,
            'owner' => $user,
            'location' => Inventory::CONSUMABLE_LOCATIONS
        ]);

        if(count($items) < count($itemIds))
            throw new PSPInvalidOperationException('Could not find one or more of the selected items. (Reload and try again?)');

        $totalPoints = 0;

        foreach($items as $item)
        {
            $points = MonsterOfTheWeekHelpers::getInventoryValue($monster->getMonster(), $item);

            if($points < 1)
                throw new PSPInvalidOperationException('One or more of the selected items are not valid for this monster. (Reload and try again?)');

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

        return $responseService->success();
    }
}
