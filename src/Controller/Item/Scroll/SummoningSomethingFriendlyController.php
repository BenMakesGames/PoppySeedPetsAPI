<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\GrammarFunctions;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/summoningScroll")
 */
class SummoningSomethingFriendlyController extends AbstractController
{
    /**
     * @Route("/{inventory}/friendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingFriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        UserRepository $userRepository, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        PetSpeciesRepository $petSpeciesRepository, PetFactory $petFactory, Squirrel3 $squirrel3
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'summoningScroll/#/friendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $pet = null;
        $gotASentinel = false;
        $gotAReusedSentinel = false;

        if($squirrel3->rngNextInt(1, 19) === 1)
        {
            $pet = $petFactory->createRandomPetOfSpecies(
                $user,
                $petSpeciesRepository->findOneBy([ 'name' => 'Sentinel' ])
            );

            $gotASentinel = true;
        }

        if($pet === null)
        {
            $pet = $petRepository->findOneBy(
                [
                    'owner' => $userRepository->findOneByEmail('the-wilds@poppyseedpets.com')
                ],
                [ 'lastInteracted' => 'ASC' ]
            );

            if($pet)
            {
                if($pet->getSpecies()->getName() === 'Sentinel')
                {
                    $gotAReusedSentinel = true;
                }
                else
                {
                    $daysInTheWild = (new \DateTimeImmutable())->diff($pet->getLastInteracted())->days;
                    $percentChanceOfTransformation = min(10, floor($daysInTheWild / 14));

                    if($squirrel3->rngNextInt(1, 100) <= $percentChanceOfTransformation)
                    {
                        $species = $squirrel3->rngNextFromArray($petSpeciesRepository->findAll());

                        if($species->getName() !== 'Sentinel' && $species->getId() != $pet->getSpecies()->getId())
                        {
                            $responseService->createActivityLog(
                                $pet,
                                ActivityHelpers::PetName($pet) . ' was altered by the energies of the wilds! They were ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ', ' .
                                'but became ' . GrammarFunctions::indefiniteArticle($species->getName()) . ' ' . $species->getName() . '!',
                                ''
                            );

                            $pet->setSpecies($species);
                        }
                    }
                }
            }
        }

        if($pet === null)
        {
            $allSpecies = $petSpeciesRepository->findAll();

            $pet = $petFactory->createRandomPetOfSpecies($user, $squirrel3->rngNextFromArray($allSpecies));

            $gotASentinel = $pet->getSpecies()->getName() === 'Sentinel';
        }

        $pet->setOwner($user);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $pet->setLocation(PetLocationEnum::DAYCARE);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) You put it in the Pet Shelter daycare...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) You put it in the Pet Shelter daycare...';
            else
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, waves "hello", then closes it again before heading to the Pet Shelter!';
        }
        else
        {
            $pet->setLocation(PetLocationEnum::HOME);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) Well... it\'s here now, I guess...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) Well... it\'s here now, I guess...';
            else
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, and walks inside!';
        }

        $em->flush();

        $responseService->setReloadPets($numberOfPetsAtHome < $user->getMaxPets());

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
