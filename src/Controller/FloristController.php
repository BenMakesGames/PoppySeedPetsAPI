<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\UserFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/florist")
 */
class FloristController extends PsyPetsController
{
    private const FLOWERS_FOR_SALE = [
        'Agrimony' => 10,
        'Bird\'s-foot Trefoil' => 10,
        'Coriander Flower' => 10,
        'Green Carnation' => 10,
        'Iris' => 10,
        'Purple Violet' => 10,
        'Red Clover' => 10,
        'Viscaria' => 10,
        'Witch-hazel' => 10,
        'Wheat' => 10,
    ];

    /**
     * @Route("/send", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function send(
        Request $request, UserRepository $userRepository, InventoryService $inventoryService, ResponseService $responseService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedFlorist() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $flowerName = $request->request->get('flower');
        $recipientId = $request->request->get('recipient');

        if(!array_key_exists($flowerName, self::FLOWERS_FOR_SALE))
            throw new UnprocessableEntityHttpException('"I don\'t have that flower available; sorry."');

        $cost = self::FLOWERS_FOR_SALE[$flowerName];

        if($cost > $user->getMoneys())
            throw new UnprocessableEntityHttpException('"It seems you don\'t have quite enough moneys."');

        $recipient = $userRepository->find($recipientId);

        if(!$recipient)
            throw new UnprocessableEntityHttpException('"Hm. I don\'t know who that is."');

        if($recipient->getId() === $this->getUser()->getId())
            $flowerName = 'Narcissus';

        $user->increaseMoneys(-10);

        $userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, 10);

        $inventoryService->receiveItem($flowerName, $recipient, $user, $user->getName() . ' bought this for you at The Florist\'s.');

        $stat = $userStatsRepository->incrementStat($user, UserStatEnum::FLOWERS_PURCHASED);

        if($stat->getValue() === 1)
            $inventoryService->receiveItem('Book of Flowers', $user, $user, 'This was delivered to you from The Florist\'s.');

        $em->flush();

        return $responseService->success();
    }

}
