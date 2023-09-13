<?php

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

    public function logExecutionTime(string $METHOD, float $executionTimeSeconds)
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