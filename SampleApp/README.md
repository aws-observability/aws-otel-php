# AWS Distro for OpenTelemetry PHP - Manual Instrumentation Integration Test Application
This sample application demonstrates how to use the AWS Distro for OpenTelemetry to instrument AWS SDK calls and validates the integration of manual instrumentation with the X-Ray backend service. This validation is done through the [AWS Distro for OpenTelemetry Testing Framework](https://github.com/aws-observability/aws-otel-test-framework).

## The Sample Application
This sample application uses Symfony v6 to expose the following routes:
1. `/` 
- Ensures the application is running
2. `/aws-sdk-call`
- Makes a call to AWS S3 to list buckets for account associated with the configured AWS credentials. 

## Running the Integration Test Sample Application

Ensure you are in the `/SampleApp` directory

run `composer install` 

`symfony server:start`