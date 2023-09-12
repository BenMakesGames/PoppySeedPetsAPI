<?php
namespace App\Controller;

use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Model\FoodWithSpice;
use App\Repository\InventoryRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\FieldGuideService;
use App\Service\PetActivity\EatingService;
use App\Service\Holidays\HalloweenService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/halloween")
 */
class HalloweenController extends AbstractController
{
    /**
     * @Route(methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getNextTrickOrTreater(
        HalloweenService $halloweenService, ResponseService $responseService, CalendarService $calendarService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$calendarService->deprecatedIsHalloween())
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        return $responseService->success($nextTrickOrTreater->getValue());
    }

    /**
     * @Route("/trickOrTreater", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTrickOrTreater(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService,
        NormalizerInterface $normalizer, CalendarService $calendarService, UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$calendarService->deprecatedIsHalloween())
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
        {
            return $responseService->success([
                'trickOrTreater' => null,
                'nextTrickOrTreater' => $nextTrickOrTreater->getValue(),
                'totalCandyGiven' => $userQuestRepository->findOrCreate($user, 'Trick-or-Treaters Treated', 0)->getValue()
            ]);
        }

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $em->flush();

        if($trickOrTreater === null)
            throw new PSPNotFoundException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!)');

        return $responseService->success([
            'trickOrTreater' => $normalizer->normalize($trickOrTreater, null, [ 'groups' => [ SerializationGroupEnum::PET_PUBLIC_PROFILE ] ]),
            'nextTrickOrTreater' => $nextTrickOrTreater->getValue(),
            'candy' => $normalizer->normalize($halloweenService->getCandy($user), null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'totalCandyGiven' => $userQuestRepository->findOrCreate($user, 'Trick-or-Treaters Treated', 0)->getValue()
        ]);
    }

    /**
     * @Route("/trickOrTreater/giveCandy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveCandy(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService,
        Request $request, InventoryRepository $inventoryRepository, CalendarService $calendarService,
        UserRepository $userRepository, Squirrel3 $squirrel3, EatingService $eatingService,
        PetActivityLogTagRepository $petActivityLogTagRepository, FieldGuideService $fieldGuideService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$calendarService->deprecatedIsHalloween())
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $candy = $inventoryRepository->find($request->request->getInt('candy'));
        $toGivingTree = $request->request->getBoolean('toGivingTree', false);

        if(!$candy || $candy->getOwner()->getId() !== $user->getId() || $candy->getLocation() !== LocationEnum::HOME)
            throw new PSPNotFoundException('The selected candy could not be found... reload and try again?');

        if(!$candy->getItem()->getFood())
            throw new PSPInvalidOperationException($candy->getItem()->getName() . ' isn\'t even edible!');

        if(!$candy->getItem()->getFood()->getIsCandy())
            throw new PSPInvalidOperationException($candy->getItem()->getName() . ' isn\'t quiiiiiiite a candy.');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
            return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ]);

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $halloweenService->resetTrickOrTreater($user);

        if($trickOrTreater === null)
        {
            $em->flush();

            throw new PSPNotFoundException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!');
        }

        if($toGivingTree)
        {
            $givingTree = $userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

            $candy
                ->setOwner($givingTree)
                ->setSellPrice(null)
                ->addComment($user->getName() . ' gave this to the Giving Tree during Halloween!')
                ->setModifiedOn()
            ;
        }
        else
        {
            $candy
                ->setOwner($trickOrTreater->getOwner())
                ->setSellPrice(null)
                ->addComment($trickOrTreater->getName() . ' received this trick-or-treating at ' . $user->getName() . '\'s house!')
                ->setModifiedOn()
            ;

            $logMessage = $trickOrTreater->getName() . ' went trick-or-treating at ' . $user->getName() . '\'s house, and received ' . $candy->getItem()->getNameWithArticle() . '!';

            $favoriteFlavorStrength = $eatingService->getFavoriteFlavorStrength($trickOrTreater, new FoodWithSpice($candy->getItem(), null));

            if($favoriteFlavorStrength > 0)
                $logMessage .= ' (' . $squirrel3->rngNextFromArray([ 'Just what they wanted!', 'Ah! The good stuff!', 'One of their favorites!' ]) . ')';

            $log = (new PetActivityLog())
                ->setPet($trickOrTreater)
                ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
                ->setIcon('ui/halloween')
                ->setEntry($logMessage)
                ->addTags($petActivityLogTagRepository->findByNames([ 'Special Event', 'Halloween' ]))
            ;

            $em->persist($log);
        }

        $reward = $halloweenService->countCandyGiven($user, $trickOrTreater, $toGivingTree);

        if($toGivingTree)
        {
            if($reward)
            {
                $responseService->addFlashMessage('The pet moves on to the next house. Also, while at the Giving Tree, you spot ' . $reward->getItem()->getNameWithArticle() . ' with your name on it! Whoa!');
            }
            else
            {
                $responseService->addFlashMessage('The pet moves on to the next house.');
            }
        }
        else
        {
            if($reward)
            {
                $responseService->addFlashMessage('Before leaving for the next house, ' . $trickOrTreater->getName() . ' hands you ' . $reward->getItem()->getNameWithArticle() . '!');
            }
            else
            {
                $responseService->addFlashMessage($trickOrTreater->getName() . ' happily takes the candy and heads off to the next house.');
            }
        }

        $fieldGuideService->maybeUnlock($user, 'Trick-or-treating', 'After giving candy to trick-or-treaters, %user:' . $user->getId() . '.Name% found this just outside their front door...');

        $em->flush();

        return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue(), 'candy' => [] ]);
    }
}
