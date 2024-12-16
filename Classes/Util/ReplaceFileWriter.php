<?php

declare(strict_types=1);

namespace Cron\CronAwslogs\Util;

final class ReplaceFileWriter
{
    public static function replaceIfActive(): void
    {
        $isActivated = filter_var(getenv('AWS_LOGS') ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$isActivated) {
            return;
        }

        // env check
        if (empty(getenv('AWS_LOG_GROUP') ?? '')) {
            echo sprintf('Required "AWS_LOG_GROUP" is empty!');
            exit(1);
        }
        self::replace();
    }

    protected static function replace(array $path = []): void
    {
        $conf = self::getConf($path);
        if (is_string($conf)) {
            return;
        }

        foreach ($conf as $key => $_data) {
            // override condition
            if ($key === \TYPO3\CMS\Core\Log\Writer\FileWriter::class) {
                self::setWriter($path);
                return;
            }
            // keep path and add current key as last item
            self::replace(array_merge($path, [$key]));
        }
    }


    protected static function &getConf(array $path): array|string
    {
        $ref = &$GLOBALS['TYPO3_CONF_VARS']['LOG'];
        foreach ($path as $crumb) {
            if (array_key_exists($crumb, $ref)) {
                $ref = &$ref[$crumb];
            } else {
                $ref = [];
            }
        }
        return $ref;
    }

    protected static function extractStream(?array $options): string
    {
        // https://docs.typo3.org/m/typo3/reference-coreapi/12.4/en-us/ApiOverview/Logging/Writers/Index.html#logging-writers-filewriter
        if (isset($options['logFile'])) {
            return str_replace('.log', '', mb_strtolower(basename($options['logFile'])));
        }
        if (isset($options['logFileInfix'])) {
            return mb_strtolower($options['logFileInfix']);
        }
        return 'global';
    }

    protected static function setWriter(array $path): void
    {
        // as reference
        $ref = &self::getConf($path);
        $options = $ref[\TYPO3\CMS\Core\Log\Writer\FileWriter::class] ?? [];
        unset($ref[\TYPO3\CMS\Core\Log\Writer\FileWriter::class]);

        // skip if "disabled"
        if (isset($options['disabled']) && $options['disabled']) {
            return;
        }

        $stream = self::extractStream($options);
        $ref[\Cron\CronAwslogs\LogWriter\CloudWatchWriter::class] = ['stream' => sprintf("/typo3/%s", $stream)];

    }
}
