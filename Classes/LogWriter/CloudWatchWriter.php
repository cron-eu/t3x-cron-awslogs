<?php

declare(strict_types=1);

namespace Cron\CronAwslogs\LogWriter;

use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use Cron\CronAwslogs\Util\CloudWatchClient;
use GuzzleHttp\Utils;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

final class CloudWatchWriter extends AbstractWriter
{
    // like typo3/cron_framework (ext), typo3/lt4u_complaint (feature) or typo3/global (no configuration)
    private string $stream = '';
    // like lt4u-test-germany-test => account + environment
    private string $group;

    public function __construct(array $options = [])
    {
        $this->group = getenv('AWS_LOG_GROUP') ?? '';
        parent::__construct($options);
    }

    public function setStream(string $stream): void
    {
        $this->stream = $stream;
    }

    public function writeLog(LogRecord $record)
    {
        // in error case, we do not log anything
        try {
            $this->putLogEvents($record);
        } catch (CloudWatchLogsException $e) {
            // retry - create stream
            $this->putLogEvents($record, true);
        }

        return $this;
    }

    private function putLogEvents(LogRecord $record, bool $createStream = false): void
    {
        if ($createStream) {
            CloudWatchClient::get()->createLogStream([
                'logGroupName' => $this->group,
                'logStreamName' => $this->stream,
            ]);
        }

        CloudWatchClient::get()->putLogEvents([
            'logEvents' => [
                [
                    'message' => Utils::jsonEncode($record->toArray()),
                    // in milliseconds
                    'timestamp' => round($record->getCreated() * 1000)
                ],
            ],
            'logGroupName' => $this->group,
            'logStreamName' => $this->stream,
        ]);
    }

}
