<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class GetPetsController extends AbstractController
{
    #[Route("/{houseSitForId}/pets", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getPets(int $houseSitForId, EntityManagerInterface $em, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        $petsAtHome = $em->getRepository(Pet::class)
            ->createQueryBuilder('p')
            ->andWhere('p.owner = :ownerId')
            ->andWhere('p.location = :home')
            ->setParameter('ownerId', $houseSitForId)
            ->setParameter('home', PetLocationEnum::HOME)
            ->getQuery()
            ->getResult();

        return $responseService->success($petsAtHome, [ SerializationGroupEnum::HOUSE_SITTER_PET ]);
    }
}