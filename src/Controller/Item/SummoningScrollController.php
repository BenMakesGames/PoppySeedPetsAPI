<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/summoningScroll")
 */
class SummoningScrollController extends PoppySeedPetsItemController
{
    private const MONSTERS = [
        'a Dragon' => [

        ],
        'a Balrog' => [

        ],
        'a Hydra' => [

        ],
        'a Basabasa' => [

        ],
        'an Ifrit' => [

        ],
        'a Cherufe' => [

        ],
    ];

    /**
     * @Route("/{inventory}/unfriendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/unfriendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'inDaycare' => false
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('');
        }
    }

    /**
     * @Route("/summoning/{inventory}/friendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingFriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        UserRepository $userRepository, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        PetSpeciesRepository $petSpeciesRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/friendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $pet = $petRepository->findOneBy(
            [
                'owner' => $userRepository->findOneByEmail('the-wilds@poppyseedpets.com')
            ],
            [ 'lastInteracted' => 'ASC' ]
        );

        if(!$pet)
        {
            $now = new \DateTimeImmutable();

            $allSpecies = $petSpeciesRepository->findAll();

            $petCount = $petRepository->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->andWhere('p.birthDate<:today')
                ->setParameter('today', $now)
                ->getQuery()
                ->getSingleScalarResult();
            ;

            $basePet = $petRepository->createQueryBuilder('p')
                ->andWhere('p.birthDate<:today')
                ->setParameter('today', $now)
                ->setMaxResults(1)
                ->setFirstResult(mt_rand(0, $petCount - 1))
                ->getQuery()
                ->getSingleResult()
            ;

            $colorA = ColorFunctions::tweakColor($basePet->getColorA());
            $colorB = ColorFunctions::tweakColor($basePet->getColorB());

            $petSkills = new PetSkills();

            $em->persist($petSkills);

            $pet = (new Pet())
                ->setOwner($user)
                ->setName(ArrayFunctions::pick_one(PetShelterPet::PET_NAMES))
                ->setSpecies(ArrayFunctions::pick_one($allSpecies))
                ->setColorA($colorA)
                ->setColorB($colorB)
                ->setFavoriteFlavor(FlavorEnum::getRandomValue())
                ->setNeeds(mt_rand(10, 12), -9)
                ->setSkills($petSkills)
            ;

            $em->persist($pet);
        }

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $pet->setInDaycare(true);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll...', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

}