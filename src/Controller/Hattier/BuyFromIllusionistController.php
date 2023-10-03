<?php

namespace App\Controller\Hattier;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\ItemRepository;
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
        'Scroll of Illusions' => [ 'moneys' => 200, 'recyclingPoints' => 100, 'bloodWine' => 2 ],
        'Blush of Life' => [ 'moneys' => 200, 'recyclingPoints' => 100, 'bloodWine' => 2 ],
        'Mysterious Seed' => [ 'moneys' => 150, 'recyclingPoints' => 75, 'bloodWine' => 1 ],
        'Tile: Giant Bat' => [ 'moneys' => 100, 'recyclingPoints' => 50, 'bloodWine' => 1 ],
        'Tile: Bats!' => [ 'moneys' => 100, 'recyclingPoints' => 50, 'bloodWine' => 1 ],
        'Magpie\'s Deal' => [ 'moneys' => 50, 'recyclingPoints' => 25, 'bloodWine' => 1 ],
        'Quinacridone Magenta Dye' => [ 'moneys' => 50, 'recyclingPoints' => 25, 'bloodWine' => 1 ],
        'On Vampires' => [ 'moneys' => 25, 'recyclingPoints' => 15, 'bloodWine' => 1 ],
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
        $payWith = $request->request->get('payWith');

        if($payWith !== 'moneys' && $payWith !== 'recyclingPoints' && $payWith !== 'bloodWine')
            throw new PSPFormValidationException('You must choose whether to pay with moneys, recycling points, or Blood Wine.');

        if(!array_key_exists($item, self::INVENTORY))
            throw new PSPFormValidationException('That item is not for sale.');

        $cost = self::INVENTORY[$item][$payWith];

        if($cost < 1)
            throw new \Exception('Cost should not be less than 1! Ben made a mistake!');

        if($payWith === 'moneys')
        {
            if($user->getMoneys() < $cost)
                throw new PSPNotEnoughCurrencyException($cost . '~~m~~', $user->getMoneys() . '~~m~~');

            $transactionService->spendMoney($user, $cost, 'Bought ' . $item . ' from the Illusionist.');
        }
        else if($payWith === 'recyclingPoints')
        {
            if($user->getRecyclePoints() < $cost)
                throw new PSPNotEnoughCurrencyException($cost . '♺', $user->getRecyclePoints() . '♺');

            $transactionService->spendRecyclingPoints($user, $cost, 'Bought ' . $item . ' from the Illusionist.');
        }
        else if($payWith === 'bloodWine')
        {
            $bloodWineId = ItemRepository::getIdByName($em, 'Blood Wine');

            if($inventoryService->loseItem($user, $bloodWineId, [ LocationEnum::HOME, LocationEnum::BASEMENT], $cost) === 0)
                throw new PSPNotFoundException('You do not have enough Blood Wine.');
        }
        else
            throw new \Exception('This should never happen. Ben made a boo-boo.');

        $inventoryService->receiveItem($item, $user, $user, 'Purchased from the Illusionist.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success();
    }
}