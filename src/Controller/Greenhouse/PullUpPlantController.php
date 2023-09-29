<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PollinatorEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\PlayerLogHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class PullUpPlantController extends AbstractController
{
    /**
     * @Route("/{plant}/pullUp", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pullUpPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3,
        InventoryService $inventoryService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That plant does not exist.');

        $logMessage = 'You pulled up the ' . $plant->getPlant()->getName() . '.';

        if($plant->getPlant()->getName() === 'Magic Beanstalk')
        {
            $flashMessage = 'Pulling up the stalk is surprisingly easy, but perhaps more surprising, you find yourself holding Magic Beans, instead of a stalk!';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Magic Beans', $user, $user, 'Received by pulling up a Magic Beanstalk, apparently. Magically.', LocationEnum::HOME);
        }
        else if($plant->getPlant()->getName() === 'Midnight Arch')
        {
            $flashMessage = 'Pulling up the arch is surprisingly easy, but perhaps more surprising, you find yourself a Mysterious Seed, instead of a stalk!';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Mysterious Seed', $user, $user, 'Received by pulling up a Midnight Arch, apparently. Magically.', LocationEnum::HOME);
        }
        else if($plant->getPlant()->getName() === 'Goat' && $plant->getIsAdult())
        {
            $flashMessage = 'The goat, startled, runs into the jungle, shedding a bit of Fluff in the process.';
            $logMessage .= ' ' . $flashMessage;
            $responseService->addFlashMessage($flashMessage);

            $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::HOME);
            if($squirrel3->rngNextInt(1, 2) === 1)
                $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::HOME);
        }

        PlayerLogHelpers::create($em, $user, $logMessage, [ 'Greenhouse' ]);

        $pollinators = $plant->getPollinators();

        if($pollinators === PollinatorEnum::BUTTERFLIES)
            $user->getGreenhouse()->setButterfliesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_1)
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_2)
            $user->getGreenhouse()->setBees2DismissedOn(new \DateTimeImmutable());

        $em->remove($plant);
        $em->flush();

        return $responseService->success();
    }
}
