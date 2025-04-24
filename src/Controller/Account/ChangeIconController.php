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


namespace App\Controller\Account;

use App\Entity\MuseumItem;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class ChangeIconController extends AbstractController
{
    #[Route("/changeIcon", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setIcon(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        $itemId = $request->request->getInt('item');

        $donated = $em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user->getId(),
            'item' => $itemId
        ]);

        if(!$donated)
            throw new PSPNotFoundException('You have not donated that item... YET! (I believe in you!)');

        $user->setIcon('items/' . $donated->getItem()->getImage());

        $em->flush();

        return $responseService->success();
    }

    #[Route("/clearIcon", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function clearIcon(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $user->setIcon(null);
        $em->flush();

        return $responseService->success();
    }
}
