<?php
namespace App\Controller\Market;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MarketService;
use App\Service\MuseumService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class GetWingedKeyController extends AbstractController
{
    #[Route("/getWingedKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getWingedKey(
        ResponseService $responseService, MarketService $marketService, MuseumService $museumService,
        InventoryService $inventoryService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$marketService->canOfferWingedKey($user))
            throw new AccessDeniedHttpException();

        UserQuestRepository::findOrCreate($em, $user, 'Received Winged Key', false)
            ->setValue(true)
        ;

        $comment = 'Begrudgingly given to ' . $user->getName() . ' by Argentelle.';

        $museumService->forceDonateItem($user, 'Winged Key', $comment);

        $inventoryService->receiveItem('Winged Key', $user, null, $comment, LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success();
    }
}
