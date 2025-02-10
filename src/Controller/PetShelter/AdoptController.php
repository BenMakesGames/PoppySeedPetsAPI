<?php
declare(strict_types=1);

namespace App\Controller\PetShelter;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Functions\ArrayFunctions;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetRepository;
use App\Functions\ProfanityFilterFunctions;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserQuestRepository;
use App\Model\PetShelterPet;
use App\Service\AdoptionService;
use App\Service\Clock;
use App\Service\HattierService;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/petShelter")]
class AdoptController extends AbstractController
{
    #[Route("/{id}/adopt", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function adoptPet(
        int $id, AdoptionService $adoptionService, Request $request, ResponseService $responseService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, Clock $clock,
        TransactionService $transactionService, IRandom $rng, PetFactory $petFactory,
        HattierService $hattierService
    )
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d');
        /** @var User $user */
        $user = $this->getUser();
        $costToAdopt = $adoptionService->getAdoptionFee($user);
        $lastAdopted = UserQuestRepository::find($em, $user, 'Last Adopted a Pet');

        if($lastAdopted && $lastAdopted->getValue() === $now)
            throw new PSPInvalidOperationException('You cannot adopt another pet today.');

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        if($user->getMoneys() < $costToAdopt)
            throw new PSPNotEnoughCurrencyException($costToAdopt . '~~m~~', $user->getMoneys() . '~~m~~');

        $petName = ProfanityFilterFunctions::filter(trim($request->request->getString('name')));

        if(\mb_strlen($petName) < 1 || \mb_strlen($petName) > 30)
            throw new PSPFormValidationException('Pet name must be between 1 and 30 characters long.');

        [$pets, $dialog] = $adoptionService->getDailyPets($user);

        /** @var PetShelterPet $petToAdopt */
        $petToAdopt = ArrayFunctions::find_one($pets, fn(PetShelterPet $p) => $p->id === $id);

        if($petToAdopt === null)
            throw new PSPFormValidationException('There is no such pet available for adoption... maybe reload and try again??');

        // let's not worry about this for now... it's a suboptimal solution
        /*
        if(!StringFunctions::isISO88591(str_replace($petToAdopt->name, '', $petName)))
            throw new PSPFormValidationException('Your pet\'s name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');
        */

        $newPet = $petFactory->createPet(
            $user, $petName, $petToAdopt->species, $petToAdopt->colorA, $petToAdopt->colorB,
            FlavorEnum::getRandomValue($rng),
            MeritRepository::getRandomAdoptedPetStartingMerit($em, $rng)
        );

        $newPet
            ->setFoodAndSafety($rng->rngNextInt(10, 12), -9)
            ->setScale($petToAdopt->scale)
        ;

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $newPet->setLocation(PetLocationEnum::DAYCARE);

        if(CalendarFunctions::isTalkLikeAPirateDay($clock->now))
        {
            StatusEffectHelpers::applyStatusEffect($em, $newPet, StatusEffectEnum::FATED_SOAKEDLY, 1);
        }
        else if(CalendarFunctions::isLeapDay($clock->now))
        {
            $newPet->addMerit(MeritRepository::findOneByName($em, 'Behatted'));

            $hat = (new Inventory())
                ->setItem(ItemRepository::findOneByName($em, 'Mermaid Egg'))
                ->setLocation(LocationEnum::WARDROBE)
                ->addComment($newPet->getName() . ' came from the Hollow Earth wearing this...')
                ->setOwner($user);

            $em->persist($hat);

            $newPet->setHat($hat);

            $hattierService->petMaybeUnlockAura(
                $newPet,
                'Leap Day\'s',
                $newPet->getName() . ' came from the Hollow Earth with a strange egg on their head...',
                $newPet->getName() . ' came from the Hollow Earth with a strange egg on their head...',
                $newPet->getName() . ' came from the Hollow Earth with an egg on their head that bore this style!'
            );
        }

        $transactionService->spendMoney($user, $costToAdopt, 'Adopted a new pet.');

        $userStatsRepository->incrementStat($user, UserStatEnum::PETS_ADOPTED, 1);

        $now = (new \DateTimeImmutable())->format('Y-m-d');

        UserQuestRepository::findOrCreate($em, $user, 'Last Adopted a Pet', $now)
            ->setValue($now)
        ;

        $em->flush();

        $costToAdopt = $adoptionService->getAdoptionFee($user);

        return $responseService->success([ 'pets' => [], 'costToAdopt' => $costToAdopt ]);
    }
}
