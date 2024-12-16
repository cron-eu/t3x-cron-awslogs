# cron_awslogs

The purpose of this extension is to replace all [TYPO3 FileWriter's](https://docs.typo3.org/m/typo3/reference-coreapi/12.4/en-us/ApiOverview/Logging/Writers/Index.html#filewriter) with our custom CloudWatchWriter pushing the logs to aws.
The extension transforms the FileWriter `logFile` and `logFileInfix` to a aws (log) `stream` configuration.
If a FileWriter is marked as `disabled` (which is the case for the TYPO3 deprecation log by default) it will be removed.

## Setup

Set follow ENV variables to activate the logging

* `AWS_LOGS=true` set, to activate the extension
* `AWS_LOG_GROUP=<ACCOUNT>-<ENVIRONMENT>` like `lt4u-test-germany-test` for log aws LogGroup

In the `ext_localconf.php`, add

```
// AWS CloudWatch - replace file writers if feature is active
\Cron\CronAwslogs\Util\ReplaceFileWriter::replaceIfActive();
```

to an extension which is loaded after others, setting FileWriter configurations.

## Credentials

The [CloudWatchLogsClient](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.CloudWatchLogs.CloudWatchLogsClient.html) uses available aws credentials or aws env vars by default.

### Docker

Create an **Access Key** in an user (managed by the IAM Tool) with the "CloudWatchLogsFullAccess" permission policy.
Set the Access Key credentials to the env vars below.

```
AWS_DEFAULT_REGION=eu-central-1
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

### AWS

In ECS the permissions should be available as an IAM TaskRole policy, for example:

```
CloudWatchLogsPolicy:
  Type: AWS::IAM::ManagedPolicy
  Properties:
    PolicyDocument:
      Version: "2012-10-17"
      Statement:
        - Effect: "Allow"
          Action:
            - "logs:CreateLogStream"
            - "logs:PutLogEvents"
          Resource: !GetAtt LogGroup.Arn
```

## Technical Details

* [SDK for PHP 3.x](https://docs.aws.amazon.com/aws-sdk-php/v3/api/) - Package to interact with AWS
* [PutLogEvents](https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-logs-2014-03-28.html#putlogevents) - to push logs to CloudWatch
