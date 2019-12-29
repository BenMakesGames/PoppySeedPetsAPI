<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\FlavorEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\AdoptionService;
use App\Service\ResponseService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
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
        UserQuestRepository $userQuestRepository
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

        return $responseService->success(
            [
                'dialog' => $dialog,
                'pets' => $pets,
                'costToAdopt' => $costToAdopt,
                'petsAtHome' => $numberOfPetsAtHome,
                'maxPets' => $user->getMaxPets(),
            ],
            SerializationGroupEnum::PET_SHELTER_PET
        );
    }

    /**
     * @Route("/{id}/adopt", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function adoptPet(
        int $id, PetRepository $petRepository, AdoptionService $adoptionService, Request $request,
        ResponseService $responseService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        UserQuestRepository $userQuestRepository
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

        $petName = trim($request->request->get('name', ''));

        if(\strlen($petName) < 1 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

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
        ;

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $newPet->setInDaycare(true);

        $em->persist($newPet);

        $user->increaseMoneys(-$costToAdopt);
        $userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $costToAdopt);
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