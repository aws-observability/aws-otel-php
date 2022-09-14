# AWS Distro for OpenTelemetry PHP

This repository contains documentation and sample apps for the AWS Distro for OpenTelemetry in PHP. It provides the AWS service integrations for traces for the [OpenTelemetry PHP](https://github.com/open-telemetry/opentelemetry-php) library. The library can be configured to support trace applications with the AWS X-Ray service. 

Please note all source code for the OpenTelemetry PHP library is upstream on the OpenTelemetry project in the OpenTelemetry PHP library repo. All features of the OpenTelemetry library are available along with its components being configured to create traces which can be viewed in the AWS X-Ray console and to allow propagation of those contexts across multiple downstream AWS services.

Once traces have been generated, they can be sent to a tracing service, like AWS X-Ray, to visualize and understand exactly what happened during the traced calls. For more information about the AWS X-Ray service, see the [AWS X-Ray Developer Guide](https://docs.aws.amazon.com/xray/latest/devguide/aws-xray.html).

To send traces to AWS X-Ray, you can use the [AWS Distro for OpenTelemetry (ADOT) Collector](https://github.com/aws-observability/aws-otel-collector). OpenTelemetry PHP exports traces from the application to the ADOT Collector. The ADOT Collector is configured with [AWS credentials for the CLI](https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-files.html), an AWS region, and which trace attributes to index so that it can send the traces to the AWS X-Ray console. Read more about the [AWS X-Ray Tracing Exporter for OpenTelemetry Collector](https://github.com/open-telemetry/opentelemetry-collector-contrib/tree/main/exporter/awsxrayexporter).

## Getting Started
See the links below for information on getting started with ADOT PHP:
- [ADOT PHP manual instrumentation](https://aws-otel.github.io/docs/getting-started/php-sdk) 

## Requirements 
PHP v7.4+ is required to use OpenTelemetry PHP. For more information on supported versions, see the [OpenTelemetry PHP package on Packagist](https://packagist.org/packages/open-telemetry/opentelemetry-php-contrib).

### Sample application - Manual instrumentation

See the [example sample application README.md](SampleApp/README.md) for setup instructions.

## Useful Links and Resources 
- [OpenTelemetry documentation](https://opentelemetry.io/) 
- [OpenTelemetry PHP core repository](https://github.com/open-telemetry/opentelemetry-php) 
- [OpenTelemetry PHP contrib repository](https://github.com/open-telemetry/opentelemetry-php-contrib) 
- [AWS Distro for OpenTelemetry Documentation](https://aws-otel.github.io/)



## Security

See [CONTRIBUTING](CONTRIBUTING.md#security-issue-notifications) for more information.

## License

This project is licensed under the Apache-2.0 License.

