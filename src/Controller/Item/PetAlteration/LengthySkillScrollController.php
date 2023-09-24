<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/lengthySkill")
 */
class LengthySkillScrollController extends AbstractController
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'lengthyScrollOfSkill');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $skill = $request->request->get('skill', '');

        if(!PetSkillEnum::isAValue($skill))
            throw new PSPFormValidationException('You gotta\' select a skill to increase!');

        if($pet->getSkills()->getStat($skill) < 10)
            throw new PSPInvalidOperationException('Only skills with at least 10 points may be selected.');

        if($pet->getSkills()->getStat($skill) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . ' already has 20 points of ' . $skill . '! It doesn\'t get higher than that!');

        $em->remove($inventory);

        $pet->getSkills()->increaseStat($skill);
        $pet->getSkills()->increaseStat($skill);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% was read ' . $inventory->getItem()->getNameWithArticle() . ', increasing their ' . ucfirst($skill) . ' to ' . $pet->getSkills()->getStat($skill) . '!')
            ->setIcon('items/scroll/lengthy-skill')
            ->addTag($petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]))
        ;

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
