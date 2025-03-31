<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/lengthySkill")]
class LengthySkillScrollController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseSkill(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'lengthyScrollOfSkill');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $skill = $request->request->getString('skill');

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
            ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Level-up' ]))
        ;

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
