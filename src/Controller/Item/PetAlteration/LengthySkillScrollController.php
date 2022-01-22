<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\PetSkillEnum;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/lengthySkill")
 */
class LengthySkillScrollController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseSkill(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'lengthyScrollOfSkill');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        $skill = $request->request->get('skill', '');

        if(!PetSkillEnum::isAValue($skill))
            throw new UnprocessableEntityHttpException('You gotta\' select a skill to increase!');

        if($pet->getSkills()->getStat($skill) < 10)
            throw new UnprocessableEntityHttpException('Only skills with at least 10 points may be selected.');

        if($pet->getSkills()->getStat($skill) >= 20)
            throw new UnprocessableEntityHttpException($pet->getName() . ' already has 20 points of ' . $skill . '! It doesn\'t get higher than that!');

        $em->remove($inventory);

        $pet->getSkills()->increaseStat($skill);
        $pet->getSkills()->increaseStat($skill);

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was read ' . $inventory->getItem()->getNameWithArticle() . ', increasing their ' . ucfirst($skill) . ' to ' . $pet->getSkills()->getStat($skill) . '!', 'items/scroll/lengthy-skill')
            ->addTag($petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]))
        ;

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
