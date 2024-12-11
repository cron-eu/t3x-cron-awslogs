# cron_awslogs

The purpose of this extension is to push all FileWriter logs to AWS CloudWatch.

## Setup

Set follow ENV variables to activate the logging

* `AWS_LOGS=true` set, to activate the extension
* `AWS_LOG_GROUP=<ACCOUNT>-<ENVIRONMENT>` like `lt4u-test-germany-test` for log group

In the `ext_localconf.php`, add

```
// AWS CloudWatch - replace file writers if feature is active
\Cron\CronAwslogs\Util\ReplaceFileWriter::replaceIfActive();
```

to an extension which is loaded after others, setting FileWriter configurations.

## Credentials

The [CloudWatchLogsClient](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.CloudWatchLogs.CloudWatchLogsClient.html) use loaded aws credentials or aws env vars by default.

### Docker

Create an **Access Key** in an user (managed by the IAM Tool) with the "CloudWatchLogsFullAccess" permission policy.
Set the Access Key credentials to the env vars below.

```
AWS_DEFAULT_REGION=eu-central-1
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

### AWS

In ECS the credentials should be available as an IAM ACN-Role.

## Technical Details

* [SDK for PHP 3.x](https://docs.aws.amazon.com/aws-sdk-php/v3/api/) - Package to interact with AWS
* [PutLogEvents](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.CloudWatchLogs.CloudWatchLogsClient.html) - to push logs to CloudWatch
