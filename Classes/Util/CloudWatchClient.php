<?php

declare(strict_types=1);

namespace Cron\CronAwslogs\Util;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class CloudWatchClient implements SingletonInterface
{
    private ?CloudWatchLogsClient $client = null;

    public static function get(): CloudWatchLogsClient
    {
        return GeneralUtility::makeInstance(CloudWatchClient::class)->getClient();
    }

    private function getClient(): CloudWatchLogsClient
    {
        if (!$this->client) {
            $options = [
                'version' => 'latest',
                'debug' => false
            ];
            // needed in docker - is not set automatic
            if (getenv('AWS_DEFAULT_REGION')) {
                $options['region'] = getenv('AWS_DEFAULT_REGION');
            }
            // taken the aws assumed role in AWS
            // OR locally, the AWS_DEFAULT_REGION, AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY env variables
            $this->client = new CloudWatchLogsClient($options);
        }
        return $this->client;
    }
}
