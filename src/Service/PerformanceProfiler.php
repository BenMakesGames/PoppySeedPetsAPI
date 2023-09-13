<?php

namespace App\Service;

use Aws\CloudWatch\CloudWatchClient;

class PerformanceProfiler
{
    private CloudWatchClient $cloudWatchClient;

    public function __construct()
    {
        $this->cloudWatchClient = new CloudWatchClient([
            'version' => 'latest',
            'region' => $_ENV['AWS_REGION'],
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ]
        ]);
    }

    public function logExecutionTime(string $className, string $methodName, float $executionTimeSeconds)
    {
        $this->cloudWatchClient->putMetricData([
            'MetricData' => [
                [
                    'MetricName' => $className . '::' . $methodName,
                    'Unit' => 'Seconds',
                    'Value' => $executionTimeSeconds,
                ],
            ],
            'Namespace' => 'PoppySeedPets/Performance',
        ]);
    }
}