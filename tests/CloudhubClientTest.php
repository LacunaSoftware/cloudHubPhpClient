<?php
namespace Tests\Lacuna\Cloudhub;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Lacuna\Cloudhub\RestClient;
use Lacuna\Cloudhub\SessionCreateRequest;
use Lacuna\Cloudhub\TrustServiceSessionTypes;
use Lacuna\Cloudhub\IdentifierTypes;
use Lacuna\Cloudhub\GetServiceAvailabilityResponse;
use Lacuna\Cloudhub\CertificateModel;
use Lacuna\Cloudhub\SessionModel;
use Lacuna\Cloudhub\TrustServiceAuthParametersModel;
use Lacuna\Cloudhub\ServiceSessionCreateRequest;
use Lacuna\Cloudhub\ServiceSessionCreateResponse;

/**
 * Verifies the CloudHub 2.0.0 wire contract of the hand-written client using a Guzzle MockHandler
 * (the PHP analog of a MockWebServer test). Run with: vendor/bin/phpunit
 */
class CloudhubClientTest extends TestCase
{
    public function testApiKeyHeaderIsSentOnGet()
    {
        $mock = new MockHandler([new Response(200, [], json_encode('BASE64CERT'))]);
        $rest = new RestClient('https://cloudhub.example/', 'MY-API-KEY', HandlerStack::create($mock));

        $result = $rest->get('https://cloudhub.example/api/sessions/certificate?session=abc');

        // The whole point of the auth: every request carries the API key header.
        $this->assertSame('MY-API-KEY', $mock->getLastRequest()->getHeaderLine('x-api-key'));
        // Default (non-associative) decode is preserved: a byte-string endpoint returns a string.
        $this->assertSame('BASE64CERT', $result);
    }

    public function testApiKeyHeaderIsSentOnPost()
    {
        $mock = new MockHandler([new Response(200, [], json_encode(['services' => []]))]);
        $rest = new RestClient('https://cloudhub.example/', 'MY-API-KEY', HandlerStack::create($mock));

        $req = new SessionCreateRequest('12345678909', 'https://localhost:8000/', TrustServiceSessionTypes::SingleSignature);
        $rest->post('https://cloudhub.example/api/sessions/', $req);

        $this->assertSame('MY-API-KEY', $mock->getLastRequest()->getHeaderLine('x-api-key'));
    }

    public function testSessionTypeSerializesAsStringNotInteger()
    {
        // CloudHub 2.0.0 breaking change: `type` is a STRING enum on the wire (was integer in 1.x).
        $req = new SessionCreateRequest('12345678909', 'https://localhost:8000/', TrustServiceSessionTypes::SingleSignature);
        $json = json_decode(json_encode($req), true);

        $this->assertSame('SingleSignature', $json['type']);
        $this->assertSame('https://localhost:8000/', $json['redirectUri']);
        $this->assertSame(300, $json['lifetimeInSeconds']); // default: 5 minutes
    }

    public function testSessionTypeConstantsAreStrings()
    {
        $this->assertSame('SingleSignature', TrustServiceSessionTypes::SingleSignature);
        $this->assertSame('MultiSignature', TrustServiceSessionTypes::MultiSignature);
        $this->assertSame('SignatureSession', TrustServiceSessionTypes::SignatureSession);
        $this->assertSame('AuthenticationSession', TrustServiceSessionTypes::AuthenticationSession);
        $this->assertSame('CPF', IdentifierTypes::CPF);
        $this->assertSame('CNPJ', IdentifierTypes::CNPJ);
    }

    public function testServiceAvailabilityResponseParsing()
    {
        $model = new GetServiceAvailabilityResponse(['discoveryAvailable' => true, 'certificateFound' => false]);
        $this->assertTrue($model->discoveryAvailable);
        $this->assertFalse($model->certificateFound);

        // certificateFound is nullable and may be absent.
        $partial = new GetServiceAvailabilityResponse(['discoveryAvailable' => true]);
        $this->assertNull($partial->certificateFound);
    }

    public function testCertificateModelParsing()
    {
        $model = new CertificateModel(['content' => 'AQID', 'alias' => 'my-cert', 'serviceName' => 'BirdID']);
        $this->assertSame('AQID', $model->content);
        $this->assertSame('my-cert', $model->alias);
        $this->assertSame('BirdID', $model->serviceName); // serviceName is a 2.0.0 addition
    }

    public function testSessionModelToleratesNullOrMissingServices()
    {
        // The spec marks SessionModel.services nullable; null/absent must not crash (PHP 8 count()).
        $this->assertSame([], (new SessionModel(['services' => null]))->services);
        $this->assertSame([], (new SessionModel([]))->services);
        $this->assertSame([], (new SessionModel(['services' => []]))->services);
    }

    public function testServiceSessionCreateRequestSerialization()
    {
        // POST /api/sessions/services/{name}: no CPF, string enum, redirectUri required. Mirrors the
        // server's SessionCreateBaseRequest (no identifierType field).
        $req = new ServiceSessionCreateRequest('https://localhost:8000/', TrustServiceSessionTypes::SingleSignature);
        $json = json_decode(json_encode($req), true);
        $this->assertSame('https://localhost:8000/', $json['redirectUri']);
        $this->assertSame('SingleSignature', $json['type']);
        $this->assertNull($json['identifier']);                 // no CPF required
        $this->assertSame(300, $json['lifetimeInSeconds']);
        $this->assertArrayNotHasKey('identifierType', $json);   // not part of this request
    }

    public function testServiceSessionCreateResponseParsing()
    {
        $model = new ServiceSessionCreateResponse([
            'serviceInfo' => ['serviceName' => 'safeid', 'provider' => 'Safeweb', 'endpoint' => 'https://safe/', 'badgeUrl' => null],
            'authUrl' => 'https://safeid.example/auth?qr=1',
        ]);
        $this->assertInstanceOf(TrustServiceAuthParametersModel::class, $model);
        $this->assertSame('Safeweb', $model->serviceInfo->provider);
        $this->assertNull($model->serviceInfo->badgeUrl);
        $this->assertSame('https://safeid.example/auth?qr=1', $model->authUrl);
    }

    public function testTrustServiceModelsTolerateNullFields()
    {
        // A real service can have a null badgeUrl (the sample renders an "Empty BadgeUrl" fallback)
        // and a null authUrl — neither may throw a null-to-string TypeError.
        $model = new SessionModel(['services' => [[
            'serviceInfo' => ['serviceName' => 'BirdID', 'provider' => 'Soluti', 'endpoint' => 'https://x/', 'badgeUrl' => null],
            'authUrl' => null,
        ]]]);
        $this->assertCount(1, $model->services);
        $svc = $model->services[0];
        $this->assertInstanceOf(TrustServiceAuthParametersModel::class, $svc);
        $this->assertSame('Soluti', $svc->serviceInfo->provider);
        $this->assertNull($svc->serviceInfo->badgeUrl);
        $this->assertNull($svc->authUrl);
    }
}
