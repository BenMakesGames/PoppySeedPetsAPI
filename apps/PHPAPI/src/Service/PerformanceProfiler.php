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


namespace App\Service;

use Aws\CloudWatch\CloudWatchClient;

class PerformanceProfiler
{
    private ?CloudWatchClient $cloudWatchClient = null;

    public function __construct()
    {
        if(!$_ENV['PERFORMANCE_LOGGING_AWS_ACCESS_KEY_ID'])
            return;

        $this->cloudWatchClient = new CloudWatchClient([
            'version' => 'latest',
            'region' => $_ENV['PERFORMANCE_LOGGING_AWS_REGION'],
            'credentials' => [
                'key' => $_ENV['PERFORMANCE_LOGGING_AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['PERFORMANCE_LOGGING_AWS_SECRET_ACCESS_KEY'],
            ]
        ]);
    }

    public function logExecutionTime(string $METHOD, float $executionTimeSeconds): void
    {
        if(!$this->cloudWatchClient)
            return;

        $this->cloudWatchClient->putMetricData([
            'MetricData' => [
                [
                    'MetricName' => $METHOD,
                    'Unit' => 'Milliseconds',
                    'Value' => $executionTimeSeconds * 1000,
                ],
            ],
            'Namespace' => 'PoppySeedPets/Performance',
        ]);
    }
}