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
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\PetColorFunctions;
use App\Service\HattierService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/resonatingBow")]
class ResonatingBowController
{
    #[Route("/{inventory}/tweakHue", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function fireHandCannon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        IRandom $rng, HattierService $hattierService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'resonatingBow');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId)
            ?? throw new PSPPetNotFoundException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $color = mb_strtoupper(mb_trim($request->request->getAlpha('color')));
        $hueShift = $request->request->getInt('hueShift');

        if($hueShift < -3 || $hueShift > 3 || $hueShift === 0)
            throw new PSPFormValidationException('Hue shift must be between -3 and 3. (And not 0 - that would be silly and a waste.)');

        if($pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) && !$pet->hasMerit(MeritEnum::BLUSH_OF_LIFE))
            throw new PSPInvalidOperationException('It seems ' . $pet->getName() . '\'s vampire bite is preventing this from working!');

        if($pet->getTool())
        {
            if($pet->getTool()->isGrayscaling())
                throw new PSPInvalidOperationException('It seems the Ambrotypic magic surrounding ' . $pet->getName() . ' is preventing this from working!');

            if($pet->getTool()->isGreenifying())
                throw new PSPInvalidOperationException('It seems the magic of ' . $pet->getName() . '\'s 5-leaf Clover is preventing this from working!');
        }

        if($color === 'A')
            $pet->setColorA(PetColorFunctions::adjustHue($pet->getColorA(), $hueShift / 30));
        else if($color === 'B')
            $pet->setColorB(PetColorFunctions::adjustHue($pet->getColorB(), $hueShift / 30));
        else
            throw new PSPFormValidationException('You forgot to choose which color to recolor!');

        $deleted = $rng->rngNextInt(1, 10) === 1;

        if($deleted)
        {
            $inventory
                ->changeItem(ItemRepository::findOneByName($em, 'Fiberglass Bow'))
                ->addComment('This was once a Resonating Bow.')
                ->setModifiedOn()
            ;

            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered! Unfortunately, a resonance feedback loop caused all the bow\'s pretty colors to freak out and explode in a flash of light. Now it\'s just a regular ol\' Fiberglass Bow again - darn :(');
        }
        else
            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered!');

        $rainbowEye = EnchantmentRepository::findOneByName($em, 'Rainboweye');

        if(!$hattierService->userHasUnlocked($user, $rainbowEye))
        {
            $hattierService->playerUnlockAura($user, $rainbowEye, 'You\'ve got an eye for color... and color has an eye for you?? Well, in any case, you received this aura by using a Resonating Bow!');
            $responseService->addFlashMessage('You\'ve got an eye for color... and color has an eye for you! (A new aura is available for you at the Hattier\'s!)');
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => $deleted ]);
    }
}
