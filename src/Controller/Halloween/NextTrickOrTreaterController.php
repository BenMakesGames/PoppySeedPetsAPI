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


namespace App\Controller\Halloween;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\CalendarFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PlayerLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\FoodWithSpice;
use App\Service\Clock;
use App\Service\FieldGuideService;
use App\Service\Holidays\HalloweenService;
use App\Service\IRandom;
use App\Service\PetActivity\EatingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/halloween")]
class NextTrickOrTreaterController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["GET"])]
    public function getNextTrickOrTreater(
        HalloweenService $halloweenService, ResponseService $responseService, Clock $clock
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!CalendarFunctions::isHalloween($clock->now))
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        return $responseService->success($nextTrickOrTreater->getValue());
    }
}
