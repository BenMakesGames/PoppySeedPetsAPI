<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\FlavorEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\StringFunctions;
use App\Model\PetShelterPet;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\AdoptionService;
use App\Service\ProfanityFilterService;
use App\Service\ResponseService;
use App\Service\StoryService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/petShelter")
 */
class PetShelterController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailablePets(
        AdoptionService $adoptionService, ResponseService $responseService, PetRepository $petRepository,
        UserQuestRepository $userQuestRepository, StoryService $storyService
    )
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d');
        $user = $this->getUser();
        $costToAdopt = $adoptionService->getAdoptionFee($user);
        $lastAdopted = $userQuestRepository->findOneBy([ 'user' => $user, 'name' => 'Last Adopted a Pet' ]);

        if($lastAdopted && $lastAdopted->getValue() === $now)
        {
            return $responseService->success([
                'costToAdopt' => $costToAdopt,
                'pets' => [],
                'dialog' => 'To make sure there are enough pets for everyone, we ask that you not adopt more than one pet per day.'
            ]);
        }

        $pets = $adoptionService->getDailyPets($user);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $dialog = "Hello! Here to adopt a new friend?\n\nIf no one catches your eye today, come back tomorrow. We get newcomers every day!\n\nSince you have so many pets in your house already, a pet you adopt will be placed into Daycare.";
        else
            $dialog = "Hello! Here to adopt a new friend?\n\nIf no one catches your eye today, come back tomorrow. We get newcomers every day!";

        $data = [
            'dialog' => $dialog,
            'pets' => $pets,
            'costToAdopt' => $costToAdopt,
            'petsAtHome' => $numberOfPetsAtHome,
            'maxPets' => $user->getMaxPets(),
        ];

        if($user->getMaxPets() > 2)
            $data['story'] = $storyService->doStory($user, StoryEnum::AN_ABUSE_OF_POWER, new ParameterBag());

        return $responseService->success(
            $data,
            [ SerializationGroupEnum::PET_SHELTER_PET, SerializationGroupEnum::STORY ]
        );
    }

    /**
     * @Route("/doStory", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function doStory(
        Request $request, StoryService $storyService, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($user->getMaxPets() <= 2)
            throw new AccessDeniedHttpException('What? What\'s going on? I\'m confused. Reload and try again.');

        $story = $storyService->doStory($user, StoryEnum::AN_ABUSE_OF_POWER, $request->request);

        return $responseService->success($story, SerializationGroupEnum::STORY);
    }

    /**
     * @Route("/{id}/adopt", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function adoptPet(
        int $id, PetRepository $petRepository, AdoptionService $adoptionService, Request $request,
        ResponseService $responseService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        UserQuestRepository $userQuestRepository, TransactionService $transactionService,
        MeritRepository $meritRepository, ProfanityFilterService $profanityFilterService
    )
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d');
        $user = $this->getUser();
        $costToAdopt = $adoptionService->getAdoptionFee($user);
        $lastAdopted = $userQuestRepository->findOneBy([ 'user' => $user, 'name' => 'Last Adopted a Pet' ]);

        if($lastAdopted && $lastAdopted->getValue() === $now)
            throw new AccessDeniedHttpException('You cannot adopt another pet today.');

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($user->getMoneys() < $costToAdopt)
            throw new UnprocessableEntityHttpException('It costs ' . $costToAdopt . ' moneys to adopt a pet, but you only have ' . $user->getMoneys() . '.');

        $petName = $profanityFilterService->filter(trim($request->request->get('name', '')));

        if(\strlen($petName) < 1 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        if(!StringFunctions::isISO88591($petName))
            throw new UnprocessableEntityHttpException('Your pet\'s name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');

        $pets = $adoptionService->getDailyPets($user);

        $petToAdopt = ArrayFunctions::find_one($pets, function(PetShelterPet $p) use($id) { return $p->id === $id; });

        if($petToAdopt === null)
            throw new UnprocessableEntityHttpException('There is no such pet available for adoption... maybe reload and try again??');

        $petSkills = new PetSkills();

        $em->persist($petSkills);

        $newPet = (new Pet())
            ->setOwner($user)
            ->setName($petName)
            ->setSpecies($petToAdopt->species)
            ->setColorA($petToAdopt->colorA)
            ->setColorB($petToAdopt->colorB)
            ->setFavoriteFlavor(FlavorEnum::getRandomValue())
            ->setNeeds(mt_rand(10, 12), -9)
            ->setSkills($petSkills)
            ->addMerit($meritRepository->getRandomAdoptedPetStartingMerit())
        ;

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $newPet->setInDaycare(true);

        $em->persist($newPet);

        $transactionService->spendMoney($user, $costToAdopt, 'Adopted a new pet.');

        $userStatsRepository->incrementStat($user, UserStatEnum::PETS_ADOPTED, 1);

        $now = (new \DateTimeImmutable())->format('Y-m-d');

        $userQuestRepository->findOrCreate($user, 'Last Adopted a Pet', $now)
            ->setValue($now)
        ;

        $em->flush();

        $costToAdopt = $adoptionService->getAdoptionFee($user);

        return $responseService->success([ 'pets' => [], 'costToAdopt' => $costToAdopt ]);
    }
}
