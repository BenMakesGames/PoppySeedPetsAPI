<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/telephone")
 */
class TelephoneController extends AbstractController
{
    #[Route("/{inventory}/pizza", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pizza(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, TransactionService $transactionService, IRandom $rng,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'telephone/#/pizza');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $orderedDeliveryFood = $userQuestRepository->findOrCreate($user, 'Ordered Delivery Food', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $orderedDeliveryFood->getValue())
            return $responseService->itemActionSuccess('You can only order delivery food once per day. (More than that is just irresponsible!)');

        $orderedDeliveryFood->setValue($today);

        if($user->getMoneys() < 45)
            throw new PSPNotEnoughCurrencyException('45~~m~~', $user->getMoneys());

        $transactionService->spendMoney($user, 45, 'Got delivery pizza');

        $pizzas = $rng->rngNextSubsetFromArray([
            'Slice of Cheese Pizza',
            'Slice of Chicken BBQ Pizza',
            'Slice of Mixed Mushroom Pizza',
            'Slice of Pineapple Pizza',
            'Slice of Spicy Calamari Pizza',
        ], 3);

        sort($pizzas);

        foreach($pizzas as $pizza)
            $inventoryService->receiveItem($pizza, $user, $user, 'You ordered this pizza over the telephone.', LocationEnum::HOME);

        $em->flush();

        return $responseService->itemActionSuccess('You ordered some pizza over the telephone. It\'s on its way-- no, wait, it\'s already here! (So speedy and so smart!)');
    }
}