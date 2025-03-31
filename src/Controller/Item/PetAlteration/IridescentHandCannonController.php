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
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetColorFunctions;
use App\Service\HattierService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/iridescentHandCannon")]
class IridescentHandCannonController extends AbstractController
{
    #[Route("/{inventory}/fire", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function fireHandCannon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetColorFunctions $petColorChangingService, IRandom $squirrel3, HattierService $hattierService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'iridescentHandCannon');

        $color = strtoupper(trim($request->request->getAlpha('color', '')));

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE) && !$pet->hasMerit(MeritEnum::BLUSH_OF_LIFE))
            throw new PSPInvalidOperationException('It seems ' . $pet->getName() . '\'s vampire bite is preventing this from working!');

        if($pet->getTool())
        {
            if($pet->getTool()->isGrayscaling())
                throw new PSPInvalidOperationException('It seems the Ambrotypic magic surrounding ' . $pet->getName() . ' is preventing this from working!');

            if($pet->getTool()->isGreenifying())
                throw new PSPInvalidOperationException('It seems the magic of ' . $pet->getName() . '\'s 5-leaf Clover is preventing this from working!');
        }

        // make sure the new hue is some minimum distance away from the old hue:
        if($color === 'A')
            $oldColor = $pet->getColorA();
        else
            $oldColor = $pet->getColorB();

        $newColor = $petColorChangingService->randomizeColorDistinctFromPreviousColor($squirrel3, $oldColor);

        if($color === 'A')
            $pet->setColorA($newColor);
        else if($color === 'B')
            $pet->setColorB($newColor);
        else
            throw new PSPFormValidationException('You forgot to choose which color to recolor!');

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
        {
            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered! (It seems their Hyperchromaticism was blasted away by the cannon, as well!)');
            $pet->removeMerit(MeritRepository::findOneByName($em, MeritEnum::HYPERCHROMATIC));
        }
        else
        {
            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered!');
        }

        $deleted = $squirrel3->rngNextInt(1, 10) === 1;

        if($deleted)
        {
            $comment = 'This was once an Iridescent Hand Cannon.';

            if($squirrel3->rngNextBool())
            {
                $comment .= ' Then it got rusty and fell apart.';

                if($squirrel3->rngNextBool())
                {
                    $comment .= ' At the same time!';

                    if($squirrel3->rngNextBool())
                        $comment .= ' (It\'s more common than you\'d think!)';
                }
            }

            $inventory
                ->changeItem(ItemRepository::findOneByName($em, 'Rusty Blunderbuss'))
                ->addComment($comment)
                ->setModifiedOn()
            ;
        }

        $rainbowEye = EnchantmentRepository::findOneByName($em, 'Rainboweye');

        if(!$hattierService->userHasUnlocked($user, $rainbowEye))
        {
            $hattierService->playerUnlockAura($user, $rainbowEye, 'You\'ve got an eye for color... and color has an eye for you?? Well, in any case, you received this aura by using an Iridescent Hand Cannon!');
            $responseService->addFlashMessage('You\'ve got an eye for color... and color has an eye for you! (A new aura is available for you at the Hattier\'s!)');
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => $deleted ]);
    }
}
