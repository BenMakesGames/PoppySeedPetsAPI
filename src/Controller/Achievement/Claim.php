<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Enum\CostOrYieldTypeEnum;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Model\TraderOfferCostOrYield;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/achievement")
 */
final class Claim extends AbstractController
{
    /**
     * @Route("/claim", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        ResponseService $responseService, Request $request, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, InventoryService $inventoryService,
        TransactionService $transactionService, Squirrel3 $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $badge = trim($request->request->get('achievement'));

        if(!$badge || !BadgeEnum::isAValue($badge))
            throw new PSPFormValidationException('Which achievement?');

        $progress = BadgeHelpers::getBadgeProgress($badge, $user, $em);

        if(!$progress['done'])
            throw new PSPInvalidOperationException('You are not eligible to claim that achievement.');

        $badge = (new UserBadge())
            ->setUser($user)
            ->setBadge($badge)
        ;

        $em->persist($badge);

        $userStatsRepository->incrementStat($user, UserStatEnum::ACHIEVEMENTS_CLAIMED);

        self::getAchievementReward($user, $progress['reward'], $inventoryService, $transactionService);

        $em->flush();

        $celebration = $rng->rngNextFromArray([
            [ 'Achievement get!', '!' ],
            [ 'Yippee!', '!' ],
            [ 'By jove, you\'ve done it!', '!' ],
            [ 'Great googly-moogly!', '!' ],
            [ 'Da-da-da-daaaaaa!', '!' ],
            [ 'Hot diggity!', '!' ],
            [ 'ooooommmmmgggggggggg!', '!!1!' ],
            [ 'Congrats!', '!' ],
            [ 'Celllll-e-brate good times-- come on!', '!' ],
            [ 'Cha-ching!', ' $_$' ],
            [ 'Coo\'... coo\'...', '.' ],
            [ 'Whaaaaaaaat?!?!', '!?!?' ],
            [ 'Say _what_ now?', '???' ],
            [ 'Sweet, fancy Moses!', '!' ],
            [ '\\*gasp\\*!', '!' ],
            [ 'Oh, hey, so this is neat:', '!' ],
            [ 'Yatta!', ' ^\\_^' ]
        ]);

        $responseService->addFlashMessage($celebration[0] . ' You received ' . self::describeYield($progress['reward']) . $celebration[1]);

        return $responseService->success();
    }

    private static function describeYield(TraderOfferCostOrYield $yield)
    {
        switch($yield->type)
        {
            case CostOrYieldTypeEnum::ITEM: return $yield->quantity . '× ' . $yield->item->getName();
            case CostOrYieldTypeEnum::MONEY: return $yield->quantity . '~~m~~';
            case CostOrYieldTypeEnum::RECYCLING_POINTS: return $yield->quantity . ' Recycling Points';
            default: throw new \Exception('Unexpected reward type "' . $yield->type . '"!? Weird! Ben should fix this!');
        }
    }

    private static function getAchievementReward(
        User $user, TraderOfferCostOrYield $yield, InventoryService $inventoryService,
        TransactionService $transactionService
    )
    {
        switch($yield->type)
        {
            case CostOrYieldTypeEnum::ITEM:
                for($i = 0; $i < $yield->quantity; $i++)
                    $inventoryService->receiveItem($yield->item, $user, $user, 'Received by claiming an Achievement.', LocationEnum::HOME, true);

                break;

            case CostOrYieldTypeEnum::MONEY:
                $transactionService->getMoney($user, $yield->quantity, 'Received by claiming an Achievement.');
                break;

            case CostOrYieldTypeEnum::RECYCLING_POINTS:
                $transactionService->getRecyclingPoints($user, $yield->quantity, 'Received by claiming an Achievement.');
                break;

            default:
                throw new \Exception('Unexpected reward type "' . $yield->type . '"!? Weird! Ben should fix this!');
        }
    }
}