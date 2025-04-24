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

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/item")]
class DragonTongueController extends AbstractController
{
    #[Route("/dragonTongue/{inventory}/speech", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSpeech(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonTongue');

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t have an adult dragon!');

        return $responseService->success([
            'greetings' => $dragon->getGreetings(),
            'thanks' => $dragon->getThanks(),
        ]);
    }

    #[Route("/dragonTongue/{inventory}/speech", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setSpeech(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        IRandom $rng
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonTongue');

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        $greetings = $request->request->all('greetings');
        $thanks = $request->request->all('thanks');

        if(count($greetings) != 2 || count($thanks) != 2)
            throw new PSPFormValidationException('You must provide two greetings and two thanks.');

        $greeting1 = trim($greetings[0]);
        $greeting2 = trim($greetings[1]);
        $thanks1 = trim($thanks[0]);
        $thanks2 = trim($thanks[1]);

        if(!$greeting1 || !$greeting2 || !$thanks1 || !$thanks2)
            throw new PSPFormValidationException('You must provide two greetings and two thanks.');

        if(mb_strlen($greeting1) > 50 || mb_strlen($greeting2) > 50 || mb_strlen($thanks1) > 50 || mb_strlen($thanks2) > 50)
            throw new PSPFormValidationException('Your greetings and thanks must be under 50 characters each. (Sorry! I don\'t make the rules! (Well, I mean, I do, but... whatever: you get it. You know how websites work.))');

        if(DragonTongueController::wordsAreEquivalent($dragon, $greeting1, $greeting2, $thanks1, $thanks2))
        {
            throw new PSPFormValidationException('Those are the exact same greetings and thanks! (You don\'t want to waste that - mm! - _delicious_ tongue on the same old words, do you?)');
        }

        $dragon
            ->setGreetings([ $greeting1, $greeting2 ])
            ->setThanks([ $thanks1, $thanks2 ])
        ;

        $em->remove($inventory);
        $em->flush();

        $ick = $rng->rngNextInt(1, 10) == 1
            ? $rng->rngNextFromArray([ '(Why is this game always like this?)', '(I don\'t suppose you have any soap?)' ])
            : $rng->rngNextFromArray([ 'Ick.', 'Gross.', '\*shudders\*', 'Ew.', 'Nasty.', '(Seriously???)' ])
        ;

        return $responseService->itemActionSuccess('The tongue, upon hearing your words, melts in your hands.\n\n' . $ick, [ 'itemDeleted' => true ]);
    }

    private static function wordsAreEquivalent(Dragon $dragon, string $greeting1, string $greeting2, string $thanks1, string $thanks2): bool
    {
        $greetings = $dragon->getGreetings();
        $thanks = $dragon->getThanks();

        return
            (
                ($greeting1 === $greetings[0] && $greeting2 === $greetings[1]) ||
                ($greeting1 === $greetings[1] && $greeting2 === $greetings[0])
            ) &&
            (
                ($thanks1 === $thanks[0] && $thanks2 === $thanks[1]) ||
                ($thanks1 === $thanks[1] && $thanks2 === $thanks[0])
            )
        ;
    }
}
