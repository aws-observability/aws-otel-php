<?php

declare(strict_types=1);

require '../../vendor/autoload.php';

use Aws\S3\S3Client;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Aws\Xray\IdGenerator;
use OpenTelemetry\Aws\Xray\Propagator;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OTLPExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

use OpenTelemetry\Instrumentation\AwsSdk\AwsSdkInstrumentation;

// Initialize Span Processor, X-Ray ID generator, Tracer Provider, and Propagator
$spanProcessor = new BatchSpanProcessor(new OTLPExporter());
$idGenerator = new IdGenerator();
$tracerProvider = new TracerProvider($spanProcessor, null, null, null, $idGenerator);
$propagator = new Propagator();

// Instantiate Aws Sdk Instrumentation class 
$awssdkinstrumentation = new AwsSdkInstrumentation();

// Set propagator and global tracer provider in the SDK instrumentation class 
$awssdkinstrumentation->setPropagator($propagator);
$awssdkinstrumentation->setTracerProvider($tracerProvider);

// Create and activate a root span 
$root = $awssdkinstrumentation->getTracer()->spanBuilder('AwsSDKInstrumentation')->setSpanKind(SpanKind::KIND_SERVER)->startSpan();
$rootScope = $root->activate();

// Activate the AWS SDK Instrumentation class. This will instrument any calls made to the AWS SDK!
$awssdkinstrumentation->activate();


$s3Client = new S3Client([
    'profile' => 'default',
    'region' => 'us-west-2', // set to your AWS region
    'version' => '2006-03-01',
]);


$result = $s3Client->putObject([
    'Bucket' => 'php-instrumentation-test-bucket',
    'Key' => 'my-key',
    'Body' => 'successfully instrumented putObject call!',
]);


$result = $s3Client->getObject([
    'Bucket' => 'php-instrumentation-test-bucket',
    'Key' => 'my-key',
]);


echo $result['Body'] . "\n";

// End the root span after all the calls to the AWS SDK have been made
$root->end();
$rootScope->detach();

