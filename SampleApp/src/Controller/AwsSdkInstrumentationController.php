<?php
namespace App\Controller;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Common\Signal\Signals;

use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;

use OpenTelemetry\Aws\Xray\IdGenerator;
use OpenTelemetry\Aws\Xray\Propagator;
use OpenTelemetry\Aws\AwsSdkInstrumentation;

use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class AwsSdkInstrumentationController
{
    private Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    #[Route('/')]
    public function home(): Response
    {
        return new Response(
            '<html><body>AWS SDK Instrumentation Sample App Running!</body></html>'
        );
    }

    private function convertOtelTraceIdToXrayFormat(String $otelTraceId) : String
    {
        $xrayTraceID = sprintf(
            "1-%s-%s",
            substr($otelTraceId, 0, 8),
            substr($otelTraceId, 8)
        );

        return $xrayTraceID;
    }

    #[Route('/outgoing-http-call')]
    public function outgoingHttpCall(): Response
    {
        // Initialize Span Processor, X-Ray ID generator, Tracer Provider, and Propagator
        $transport = (new GrpcTransportFactory())->create('http://127.0.0.1:4317' . OtlpUtil::method(Signals::TRACE));
        $exporter = new SpanExporter($transport);
        $spanProcessor = new SimpleSpanProcessor($exporter);

        $idGenerator = new IdGenerator();
        $tracerProvider = new TracerProvider($spanProcessor, null, null, null, $idGenerator);
        $propagator = new Propagator();
        $tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
        $carrier = [];

        // Create and activate root span
        $root = $tracer
                ->spanBuilder('outgoing-http-call')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->startSpan();
        $rootScope = $root->activate();

        $httpSpan = $tracer
                ->spanBuilder('get-request')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->startSpan();
        $httpScope = $httpSpan->activate();

        // Make HTTP request
        $client = HttpClient::create(); 

        $awsHttpUrl = 'https://aws.amazon.com/';

        $response = $client->request(
            'GET',
            $awsHttpUrl
        );

        $propagator->inject($carrier);

        $root->setAttributes([
            "http.method" => $this->request->getMethod(),
            "http.url" => $this->request->getUri(),
            "http.status_code" => $response->getStatusCode()
        ]);

        $httpSpan->setAttributes([
            "http.method" => $this->request->getMethod(),
            "http.url" => $awsHttpUrl,
            "http.status_code" => $response->getStatusCode()
        ]);

        $httpSpan->end();
        $httpScope->detach();

        $root->end();
        $rootScope->detach();

        $traceId = $this->convertOtelTraceIdToXrayFormat(
            $root->getContext()->getTraceId()
        );

        return new JsonResponse(
            ['traceId' => $traceId]
        );
    }

    #[Route('/aws-sdk-call')]
    public function awsSdkCall(): Response
    {
        // Initialize Span Processor, X-Ray ID generator, Tracer Provider, and Propagator
        $transport = (new GrpcTransportFactory())->create('http://127.0.0.1:4317' . OtlpUtil::method(Signals::TRACE));
        $exporter = new SpanExporter($transport);

        $spanProcessor = new SimpleSpanProcessor($exporter);
        $idGenerator = new IdGenerator();
        $tracerProvider = new TracerProvider($spanProcessor, null, null, null, $idGenerator);
        $propagator = new Propagator();

        // Create new instance of AWS SDK Instrumentation class
        $awssdkinstrumentation = new  AwsSdkInstrumentation();

        // Configure AWS SDK Instrumentation with Propagator and set Tracer Provider (created above)
        $awssdkinstrumentation->setPropagator($propagator);
        $awssdkinstrumentation->setTracerProvider($tracerProvider);

        // Create and activate root span
        $root = $awssdkinstrumentation
                ->getTracer()
                ->spanBuilder('AwsSDKInstrumentation')
                ->setSpanKind(SpanKind::KIND_SERVER)
                ->startSpan();
        $rootScope = $root->activate();

        $root->setAttributes([
            "http.method" => $this->request->getMethod(),
            "http.url" => $this->request->getUri(),
        ]);

        // Initialize all AWS Client instances
        $s3Client = new S3Client([
            'region' => 'us-west-2',
            'version' => '2006-03-01'
        ]);

        // Pass client instances to AWS SDK
        $awssdkinstrumentation->instrumentClients([$s3Client]);

        // Activate Instrumentation -- all AWS Client calls will be instrumented
        $awssdkinstrumentation->activate();

        // Make S3 client call
        try{
            $result = $s3Client->listBuckets();

            echo $result['Body'] . "\n";

            $root->setAttributes([
                'http.status_code' => $result['@metadata']['statusCode'],
            ]);

        } catch (AwsException $e){
            $root->recordException($e);
        }

        // End the root span after all the calls to the AWS SDK have been made
        $root->end();
        $rootScope->detach();

        $traceId = $this->convertOtelTraceIdToXrayFormat(
            $root->getContext()->getTraceId()
        );

        return new JsonResponse(
            ['traceId' => $traceId]
        );
    }
}