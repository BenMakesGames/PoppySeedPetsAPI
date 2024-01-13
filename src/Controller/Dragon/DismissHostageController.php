<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Functions\PlayerLogFactory;
use App\Service\DragonHostageService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/dragon")]
class DismissHostageController extends AbstractController
{
    #[Route("/dismissHostage", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dismissHostage(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        DragonHostageService $dragonHostageService, NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon || !$dragon->getHostage())
            throw new PSPNotFoundException('You don\'t have a dragon hostage...');

        $hostage = $dragon->getHostage();

        $loot = $dragonHostageService->generateLoot($hostage->getType());

        $em->remove($hostage);
        $dragon->setHostage(null);

        $responseService->addFlashMessage($loot->flashMessage);

        $inventoryService->receiveItem($loot->item, $dragon->getOwner(), $dragon->getOwner(), $loot->comment, LocationEnum::HOME, false);

        PlayerLogFactory::create(
            $em,
            $user,
            'You ushered a "hostage" out of your Dragon Den. ' . $loot->flashMessage,
            [ 'Dragon Den' ]
        );

        $em->flush();

        return $responseService->success(DragonHelpers::createDragonResponse($em, $normalizer, $user, $dragon));
    }
}
