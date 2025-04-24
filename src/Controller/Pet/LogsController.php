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


namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class LogsController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/logs/calendar/{year}/{month}", methods: ["GET"], requirements: ["pet" => "\d+", "year" => "\d+", "month" => "\d+"])]
    public function logCalendar(
        ResponseService $responseService, EntityManagerInterface $em,

        // route arguments:
        Pet $pet, ?int $year = null, ?int $month = null
    ): JsonResponse
    {
        if($year === null && $month === null)
        {
            $year = (int)date('Y');
            $month = (int)date('n');
        }

        if($month < 1 || $month > 12)
            throw new PSPFormValidationException('"month" must be between 1 and 12!');

        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        $results = self::findLogsForPetByDate($em, $pet, $year, $month);

        return $responseService->success([
            'year' => $year,
            'month' => $month,
            'calendar' => $results
        ]);
    }

    private static function findLogsForPetByDate(EntityManagerInterface $em, Pet $pet, int $year, int $month): array
    {
        $firstDayOfMonth = $year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01';

        // ... >_>
        if($month == 12)
            $firstDayOfNextMonth = ($year + 1) . '-01-01';
        else
            $firstDayOfNextMonth = $year . '-' . str_pad((string)($month + 1), 2, '0', STR_PAD_LEFT) . '-01';

        // TODO: replace with SimpleDb access
        $qb = $em->getRepository(PetActivityLog::class)->createQueryBuilder('l')
            ->select('COUNT(l) AS quantity,SUM(l.interestingness)/COUNT(l) AS averageInterestingness, DATE(l.createdOn) AS yearMonthDay')
            ->andWhere('l.pet = :pet')
            ->andWhere('l.createdOn >= :firstDayOfMonth')
            ->andWhere('l.createdOn < :firstDayOfNextMonth')
            ->addGroupBy('yearMonthDay')

            ->setParameter('pet', $pet)
            ->setParameter('firstDayOfMonth', $firstDayOfMonth)
            ->setParameter('firstDayOfNextMonth', $firstDayOfNextMonth)
        ;

        return $qb
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/logs", methods: ["GET"], requirements: ["pet" => "\d+"])]
    public function logs(
        Pet $pet, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService,
        Request $request
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        $petActivityLogsFilterService->addRequiredFilter('pet', $pet->getId());

        return $responseService->success(
            $petActivityLogsFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS ]
        );
    }
}
