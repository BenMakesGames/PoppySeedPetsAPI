<?php
namespace App\Controller;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/petShelter")
 */
class PetShelterController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailablePets(
        PetSpeciesRepository $petSpeciesRepository, ResponseService $responseService, PetRepository $petRepository,
        UserQuestRepository $userQuestRepository
    )
    {
        $now = date('Y-m-d');
        $user = $this->getUser();
        $costToAdopt = 50 * pow(2, count($user->getPets()) - 1);

        if(date('H') == 23)
        {
            return $responseService->success([
                'costToAdopt' => $costToAdopt,
                'pets' => [],
                'dialog' => 'Sorry, we\'re closed right now. Can you come back in an hour?'
            ]);
        }

        $lastAdopted = $userQuestRepository->findOneBy([ 'user' => $user, 'name' => 'Last Adopted a Pet' ]);

        if($lastAdopted && $lastAdopted->getValue() === $now)
        {
            return $responseService->success([
                'costToAdopt' => $costToAdopt,
                'pets' => [],
                'dialog' => 'To make sure there are enough pets for everyone, we ask that you not adopt more than one pet per day.'
            ]);
        }

        mt_srand($user->getDailySeed());

        $numPets = mt_rand(4, 8);

        $petCount = $petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $now)
            ->getQuery()
            ->getSingleScalarResult();
        ;

        $petNames = [
            "Aalina", "Aaron", "Abrahil", "Aedoc", "Aelfric", "Alain", "Alda", "Alienora", "Aliette", "Artaca",
            "Aureliana", "Batu", "Belka", "Bezzhen", "Biedeluue", "Blicze", "Ceinguled", "Ceri", "Ceslinus", "Christien",
            "Clement", "Cyra", "Czestobor", "Dagena", "Denyw", "Disideri", "Eileve", "Emilija", "Enim", "Enynny",
            "Erasmus", "Eve", "Felix", "Fiora", "Fluri", "Frotlildis", "Galine", "Gennoveus", "Genoveva", "Giliana",
            "Godelive", "Gubin", "Idzi", "Jadviga", "Jehanne", "Kaija", "Kain", "Kima", "Kint", "Kirik",
            "Klara", "Kryspin", "Leodhild", "Leon", "Levi", "Lowri", "Lucass", "Ludmila", "Maccos", "Maeldoi",
            "Magdalena", "Makrina", "Malik", "Margaret", "Marsley", "Masayasu", "Mateline", "Mathias", "Maurifius", "Meduil",
            "Melita", "Meoure", "Merewen", "Milesent", "Milian", "Mold", "Montgomery", "Morys", 'Newt', "Nicholina",
            "Nilus", "Noe", "Oswyn", "Paperclip", "Perkhta", "Pesczek", "Regina", "Reina", "Rimoete", "Rocatos",
            "Rozalia", "Rum", "Runne", "Ryd", "Saewine", "Sandivoi", "Skenfrith", "Sulimir", "Sybil", "Talan",
            "Tede", "Tephaine", "Tetris", "Tiecia", "Toregene", "Trenewydd", "Usk", "Vasilii", "Vitseslav", "Vivka",
            "Wrexham", "Ysabeau", "Ystradewel", "Zofija", "Zygmunt"
        ];

        $pets = [];

        $allSpecies = $petSpeciesRepository->findAll();

        for($i = 0; $i < $numPets; $i++)
        {
            $basePet = $petRepository->createQueryBuilder('p')
                ->andWhere('p.birthDate<:today')
                ->setParameter('today', $now)
                ->setMaxResults(1)
                ->setFirstResult(mt_rand(0, $petCount - 1))
                ->getQuery()
                ->getSingleResult()
            ;

            $colorA = $this->tweakColor($basePet->getColorA());
            $colorB = $this->tweakColor($basePet->getColorB());

            $pets[] = (new Pet())
                ->setSpecies(ArrayFunctions::pick_one($allSpecies))
                ->setName(ArrayFunctions::pick_one($petNames))
                ->setColorA($colorA)
                ->setColorB($colorB)
            ;
        }

        if(count($user->getPets()) >= $user->getMaxPets())
            $dialog = 'You already have the maximum allowed number of pets.';
        else
            $dialog = "Hello! Here to adopt a new friend?\n\nIf no one catches your eye today, come back tomorrow. We get newcomers every day!";

        return $responseService->success([ 'dialog' => $dialog, 'pets' => $pets, 'costToAdopt' => $costToAdopt ], SerializationGroupEnum::PET_SHELTER_PET);
    }

    /**
     * @Route("/{id}/adopt", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function adoptPet(
        int $id, PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository, Request $request,
        ResponseService $responseService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        UserQuestRepository $userQuestRepository
    )
    {
        if(date('H') == 23)
            throw new UnprocessableEntityHttpException('Pets cannot be adopted during this hour of the day.');

        $user = $this->getUser();

        if(count($user->getPets()) >= $user->getMaxPets())
            throw new UnprocessableEntityHttpException('You cannot adopt any more pets.');

        $costToAdopt = 50 * pow(2, count($user->getPets()) - 1);

        if($user->getMoneys() < $costToAdopt)
            throw new UnprocessableEntityHttpException('It costs ' . $costToAdopt . ' moneys to adopt a pet, but you only have ' . $user->getMoneys() . '.');

        mt_srand($user->getDailySeed());
        $now = date('Y-m-d');

        $numPets = mt_rand(4, 8);

        if($id >= $numPets)
            throw new UnprocessableEntityHttpException('There is no such pet available for adoption...');

        $petName = trim($request->request->get('name', ''));

        if(\strlen($petName) < 1 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        $petCount = $petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $now)
            ->getQuery()
            ->getSingleScalarResult();
        ;

        $petToAdopt = null;

        $allSpecies = $petSpeciesRepository->findAll();

        for($i = 0; $i <= $id; $i++)
        {
            $basePet = $petRepository->createQueryBuilder('p')
                ->andWhere('p.birthDate<:today')
                ->setParameter('today', $now)
                ->setMaxResults(1)
                ->setFirstResult(mt_rand(0, $petCount - 1))
                ->getQuery()
                ->getSingleResult()
            ;

            $colorA = $this->tweakColor($basePet->getColorA());
            $colorB = $this->tweakColor($basePet->getColorB());

            $petToAdopt = (new Pet())
                ->setSpecies(ArrayFunctions::pick_one($allSpecies))
                ->setColorA($colorA)
                ->setColorB($colorB)
            ;
        }

        $petSkills = new PetSkills();

        $em->persist($petSkills);

        $petToAdopt
            ->setOwner($user)
            ->setName($petName)
            // species, colorA, and colorB are already taken care of
            ->setNeeds(mt_rand(10, 12), -9)
            ->setSkills($petSkills)
        ;

        $em->persist($petToAdopt);

        $user->increaseMoneys(-$costToAdopt);
        $userStatsRepository->incrementStat($user, UserStatEnum::TOTAL_MONEYS_SPENT, $costToAdopt);
        $userQuestRepository->findOrCreate($user, 'Last Adopted a Pet', $now);

        $em->flush();

        $costToAdopt = 50 * pow(2, count($user->getPets()) - 1);

        return $responseService->success([ 'pets' => [], 'costToAdopt' => $costToAdopt ]);
    }

    private function tweakColor(string $color): string
    {
        $newColor = '';

        for($i = 0; $i < 3; $i++)
        {
            $part = hexdec($color[$i * 2] . $color[$i * 2 + 1]);    // get color part as decimal
            $part += mt_rand(-12, 12);                              // randomize
            $part = max(0, min(255, $part));                        // keep between 0 and 255
            $part = str_pad(dechex($part), 2, '0', STR_PAD_LEFT);   // turn back into hex

            $newColor .= $part;
        }

        return $newColor;
    }
}