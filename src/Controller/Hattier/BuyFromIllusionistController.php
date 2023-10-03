<?php

namespace App\Controller\Hattier;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/illusionist")
 */
class BuyFromIllusionistController extends AbstractController
{
    private const INVENTORY = [
        'Scroll of Illusions' => 200,
        'Blush of Life' => 200,
        'On Vampires' => 25,
    ];

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Request $request, TransactionService $transactionService, InventoryService $inventoryService,
        EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $item = $request->request->get('item');

        if(!array_key_exists($item, self::INVENTORY))
            throw new PSPFormValidationException('That item is not for sale.');

        $cost = self::INVENTORY[$item];

        if($user->getMoneys() < $cost)
            throw new PSPNotEnoughCurrencyException($cost . '~~m~~', $user->getMoneys() . '~~m~~');

        $transactionService->spendMoney($user, $cost, 'Bought ' . $item . ' from the Illusionist.');

        $inventoryService->receiveItem($item, $user, $user, 'Purchased from the Illusionist.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success();
    }
}