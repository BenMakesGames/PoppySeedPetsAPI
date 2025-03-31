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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\EnchantmentRepository;
use App\Functions\UserQuestRepository;
use App\Service\HattierService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/firework")]
class FireworkController extends AbstractController
{
    #[Route("/{inventory}/light", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function light(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HattierService $hattierService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'firework/#/light');

        $itemName = $inventory->getItem()->getName();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
            return $responseService->itemActionSuccess('It makes a lot of lovely sparkles, and it\'s very nice and all, but something tells you there\'s a secret to this firework you haven\'t quite unlocked yet...' . "\n\n" . '_(Try again when you\'ve discovered the Hattier... whatever _that_ is!)_');

        $aura = EnchantmentRepository::findOneByName($em, [
            'Blue Firework' => '& Blue Fireworks',
            'Red Firework' => '& Red Fireworks',
            'White Firework' => '& White Fireworks',
            'Yellow Firework' => '& Yellow Fireworks',
        ][$itemName]);

        if($hattierService->userHasUnlocked($user, $aura))
            return $responseService->itemActionSuccess('You\'ve already unlocked the "' . $aura->getAura()->getName() . '" hat styling.');

        $unlockedAnyFirework = UserQuestRepository::findOrCreate($em, $user, 'Unlocked Any Firework Styling', false);

        if(!$unlockedAnyFirework->getValue())
        {
            $hattierService->playerUnlockAura($user, $aura, 'You set off a ' . $itemName . ', and that... somehow... unlocked this?? (Seems pretty video-game-y, if you ask me!)');
            $unlockedAnyFirework->setValue(true);
        }
        else
            $hattierService->playerUnlockAura($user, $aura, 'You set off a ' . $itemName . ', and that... somehow... unlocked this?? Sure.');

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('It makes a lot of lovely sparkles, AND: a new hat styling has been made available at the Hattier!', ['itemDeleted' => true]);
    }
}
