<?php
namespace App\Controller;

use App\Entity\PetActivityLog;
use App\Enum\SerializationGroupEnum;
use App\Functions\GrammarFunctions;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\HalloweenService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/halloween")
 */
class HalloweenController extends PoppySeedPetsController
{
    /**
     * @Route("/trickOrTreater", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTrickOrTreater(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService
    )
    {
        $user = $this->getUser();

        if(!$halloweenService->isHalloween())
            throw new AccessDeniedHttpException('It isn\'t Halloween!');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
        {
            return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ]);
        }

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $em->flush();

        if($trickOrTreater === null)
            throw new UnprocessableEntityHttpException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!');

        return $responseService->success([ 'trickOrTreater' => $trickOrTreater, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ], SerializationGroupEnum::PET_PUBLIC_PROFILE);
    }

    /**
     * @Route("/trickOrTreater/giveCandy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveCandy(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService,
        Request $request, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        if(!$halloweenService->isHalloween())
            throw new AccessDeniedHttpException('It isn\'t Halloween!');

        $candy = $inventoryRepository->find($request->request->getInt('item'));

        if(!$candy || $candy->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('The selected candy could not be found... reload and try again?');

        if(!$candy->getItem()->getFood())
            throw new UnprocessableEntityHttpException($candy->getItem()->getName() . ' isn\'t even edible!');

        if(!$candy->getItem()->getFood()->isCandy())
            throw new UnprocessableEntityHttpException($candy->getItem()->getName() . ' isn\'t quiiiiiiite a candy.');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
            return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ]);

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $halloweenService->resetTrickOrTreater($user);

        if($trickOrTreater === null)
        {
            $em->flush();

            throw new UnprocessableEntityHttpException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!');
        }

        $candy
            ->setOwner($trickOrTreater->getOwner())
            ->addComment($trickOrTreater->getName() . ' received this trick-or-treating at ' . $user->getName() . '\'s house!')
            ->setModifiedOn()
        ;

        $reward = $halloweenService->countCandyGiven($user, $trickOrTreater);

        if($reward)
        {
            $responseService->addActivityLog(
                (new PetActivityLog())->setEntry('Before leaving for the next house, ' . $trickOrTreater->getName() . ' hands you ' . GrammarFunctions::indefiniteArticle($reward->getItem()->getName()) . ' ' . $reward->getItem()->getName() . '!')
            );
        }
        else
        {
            $responseService->addActivityLog(
                (new PetActivityLog())->setEntry($trickOrTreater->getName() . ' happily takes the candy and heads off to the next house.')
            );
        }

        $em->flush();

        return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ]);
    }
}