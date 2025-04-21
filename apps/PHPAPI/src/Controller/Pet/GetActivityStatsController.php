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
use App\Entity\User;
use App\Enum\PetActivityStatEnum;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\PetActivityStatsService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class GetActivityStatsController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/activityStats", methods: ["GET"], requirements: ["pet" => "\d+"])]
    public function activityStats(
        Pet $pet, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        $stats = $pet->getPetActivityStats();

        if($stats === null)
            return $responseService->success(null);

        $data = [
            'byTime' => [],
            'byActivity' => [],
            'byActivityCombined' => [],
        ];

        $byTimeTotal = 0;
        $byActivityTotal = 0;
        $byActivityCombinedTotal = 0;

        foreach(PetActivityStatEnum::getValues() as $stat)
        {
            if(in_array($stat, PetActivityStatsService::STATS_THAT_CANT_FAIL))
            {
                $data['byActivity'][] = [
                    'value' => $stats->{'get' . $stat}(),
                    'deleted' => 0,
                    'label' => PetActivityStatsService::STAT_LABELS[$stat],
                    'color' => PetActivityStatsService::STAT_COLORS[$stat]
                ];

                $data['byActivityCombined'][] = [ 'value' => $stats->{'get' . $stat}(), 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

                $byActivityTotal += $stats->{'get' . $stat}();
                $byActivityCombinedTotal += $stats->{'get' . $stat}();
            }
            else
            {
                $success = $stats->{'get' . $stat . 'success'}();
                $failure = $stats->{'get' . $stat . 'failure'}();

                $data['byActivity'][] = [
                    'value' => $success + $failure,
                    'deleted' => $failure,
                    'label' => PetActivityStatsService::STAT_LABELS[$stat],
                    'color' => PetActivityStatsService::STAT_COLORS[$stat]
                ];

                $data['byActivityCombined'][] = [ 'value' => $success + $failure, 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

                $byActivityTotal += $success + $failure;
                $byActivityCombinedTotal += $success + $failure;
            }

            $data['byTime'][] = [ 'value' => $stats->{'get' . $stat . 'time'}(), 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

            $byTimeTotal += $stats->{'get' . $stat . 'time'}();
        }

        $data['byActivity'] = array_map(function($a) use($byActivityTotal) {
            return [
                'label' => $a['label'],
                'value' => $a['value'] > 0 ? ($a['value'] / $byActivityTotal) : 0,
                'percentDeleted' => $a['value'] > 0 ? ($a['deleted'] / $a['value']) : 0,
                'color' => $a['color'],
            ];
        }, $data['byActivity']);

        $data['byActivityCombined'] = array_map(function($a) use($byActivityCombinedTotal) {
            return [
                'label' => $a['label'],
                'value' => $byActivityCombinedTotal > 0 ? ($a['value'] / $byActivityCombinedTotal) : 0,
                'color' => $a['color'],
            ];
        }, $data['byActivityCombined']);

        $data['byTime'] = array_map(function($a) use($byTimeTotal) {
            return [
                'label' => $a['label'],
                'value' => $byTimeTotal > 0 ? ($a['value'] / $byTimeTotal) : 0,
                'color' => $a['color']
            ];
        }, $data['byTime']);

        // the chart order is important; the transition from one chart to the next (in order) teaches what the charts mean
        return $responseService->success([
            [
                'title' => 'Activities, by Time Spent',
                'data' => $data['byTime'],
            ],
            [
                'title' => 'Activities, by Count',
                'data' => $data['byActivityCombined'],
            ],
            [
                'title' => 'Activity Success vs Failure',
                'data' => $data['byActivity'],
            ],
        ]);
    }
}
