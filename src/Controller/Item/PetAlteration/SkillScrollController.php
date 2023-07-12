<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class SkillScrollController extends AbstractController
{
    /**
     * @Route("/brawlSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseBrawl(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::BRAWL);
    }

    /**
     * @Route("/craftsSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseCrafts(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::CRAFTS);
    }

    /**
     * @Route("/musicSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseMusic(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::MUSIC);
    }

    /**
     * @Route("/natureSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseNature(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::NATURE);
    }

    /**
     * @Route("/scienceSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseScience(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::SCIENCE);
    }

    /**
     * @Route("/stealthSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseStealth(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::STEALTH);
    }

    /**
     * @Route("/umbraSkillScroll/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseUmbra(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        return $this->doSkillScroll($inventory, $request, $em, $petRepository, $responseService, PetSkillEnum::UMBRA);
    }

    private function doSkillScroll(
        Inventory $inventory, Request $request, EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService, string $skill
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, $skill . 'SkillScroll');

        if(!PetSkillEnum::isAValue($skill))
            throw new UnprocessableEntityHttpException('Not a valid skill.');

        $user = $this->getUser();
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSkills()->getStat($skill) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . ' already has 20 points of ' . $skill . '! It doesn\'t get higher than that!');

        $em->remove($inventory);

        $pet->getSkills()->increaseStat($skill);

        $em->flush();

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was read ' . $inventory->getItem()->getNameWithArticle() . ', increasing their ' . ucfirst($skill) . ' to ' . $pet->getSkills()->getStat($skill) . '!', 'items/scroll/skill/' . $skill);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
