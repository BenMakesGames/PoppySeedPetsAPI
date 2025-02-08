<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class SkillScrollController extends AbstractController
{
    #[Route("/brawlSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseBrawl(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::BRAWL);
    }

    #[Route("/craftsSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseCrafts(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::CRAFTS);
    }

    #[Route("/musicSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseMusic(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::MUSIC);
    }

    #[Route("/natureSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseNature(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::NATURE);
    }

    #[Route("/scienceSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseScience(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::SCIENCE);
    }

    #[Route("/stealthSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseStealth(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::STEALTH);
    }

    #[Route("/arcanaSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseArcana(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::ARCANA);
    }

    private function doSkillScroll(
        Inventory $inventory, Request $request, EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService, string $skill
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, $skill . 'SkillScroll');

        if(!PetSkillEnum::isAValue($skill))
            throw new PSPFormValidationException('Not a valid skill.');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSkills()->getStat($skill) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . ' already has 20 points of ' . $skill . '! It doesn\'t get higher than that!');

        $em->remove($inventory);

        $pet->getSkills()->increaseStat($skill);

        $em->flush();

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% was read ' . $inventory->getItem()->getNameWithArticle() . ', increasing their ' . ucfirst($skill) . ' to ' . $pet->getSkills()->getStat($skill) . '!')
            ->setIcon('items/scroll/skill/' . $skill);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
