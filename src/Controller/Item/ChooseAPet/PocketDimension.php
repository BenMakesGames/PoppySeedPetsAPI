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


namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/pocketDimension")]
class PocketDimension extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pocketDimension');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $merit = $em->getRepository(Merit::class)->findOneBy([ 'name' => MeritEnum::BIGGER_LUNCHBOX ]);

        if(!$merit)
            throw new \Exception("Ben forgot to put the Bigger Lunchbox merit in the database! Agk!");

        if($pet->hasMerit($merit->getName()))
            throw new PSPFormValidationException($pet->getName() . '\'s lunchbox is already bigger on the inside!');

        $pet->addMerit($merit);

        PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name%'s lunchbox got bigger!")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
            ->setChanges($petChanges->compare($pet))
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()}'s lunchbox got bigger... but only on the inside? \*shrugs\* Good enough.",
            [ 'itemDeleted' => true ]
        );
    }
}