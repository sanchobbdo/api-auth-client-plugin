<?php

namespace Sancho\Tests\Guzzle\Plugin\ApiAuth;

use Guzzle\Http\Message\RequestFactory;
use Guzzle\Common\Event;
use Sancho\Guzzle\Plugin\ApiAuth\ApiAuthPlugin;

class ApiAuthPluginTest extends \PHPUnit_Framework_TestCase
{
    const TIMESTAMP = '1327274290';
    const CANONICAL_STRING = "text/plain,1B2M2Y8AsgTpgAmY7PhCfg==,/resource.xml?foo=bar&bar=foo,Mon, 23 Jan 1984 03:29:56 GMT";

    protected $config = array(
        'access_id' => '1044',
        'secret_key' => 'ybqnM8UFztOwDfLOnsLlpUi+weSLvhiA5AigjUmRcWZ9dRSj1cnGWlnGKSAI\n+VT2VcdmQ3F61lfumx133MWcHw=='
    );

    protected function getRequest()
    {
        return RequestFactory::getInstance()->create(
            'PUT',
            '/resource.xml?foo=bar&bar=foo',
            array(
                'Content-type' => "text/plain",
                'Content-MD5' => "1B2M2Y8AsgTpgAmY7PhCfg==",
                'Date' => "Mon, 23 Jan 1984 03:29:56 GMT"
            )
        );
    }

    public function testSubscribesToEvents()
    {
        $events = ApiAuthPlugin::getSubscribedEvents();
        $this->assertArrayHasKey('request.before_send', $events);
    }

    public function testAcceptsConfigurationData()
    {
        $p = new ApiAuthPlugin($this->config);

        // Access the config object
        $class = new \ReflectionClass($p);
        $property = $class->getProperty('config');
        $property->setAccessible(true);
        $config = $property->getValue($p);

        $this->assertEquals($this->config['access_id'], $config['access_id']);
        $this->assertEquals($this->config['secret_key'], $config['secret_key']);
    }


    public function testShouldSetTheDateHeaderIfOneIsNotAlreadyPresent()
    {
        $request = RequestFactory::getInstance()->create(
            'PUT',
            'http://test.co/resource.xml?foo=bar&bar=foo',
            array(
                'Content-type' => "text/plain",
                'Content-MD5' => "e59ff97941044f85df5297e1c302d260",
            )
        );
        $event = new Event(array(
            'request' => $request,
            'timestamp' => self::TIMESTAMP
        ));

        $p = new ApiAuthPlugin($this->config);
        $p->onRequestBeforeSend($event);

        $this->assertNotEmpty($event['request']->getHeader('Date'));
    }

    public function testMD5HeaderNotAlreadyProvidedShouldCalculateForEmptyString()
    {
        $request = RequestFactory::getInstance()->create(
            'PUT',
            'http://test.co/resource.xml?foo=bar&bar=foo',
            array(
                'Content-type' => "text/plain",
                'Date' => http_date()
            )
        );
        $event = new Event(array(
            'request' => $request,
            'timestamp' => self::TIMESTAMP
        ));

        $p = new ApiAuthPlugin($this->config);
        $p->onRequestBeforeSend($event);

        $this->assertEquals(
            (string) $event['request']->getHeader('Content-MD5'),
            '1B2M2Y8AsgTpgAmY7PhCfg=='
        );
    }

    public function testMD5HeaderShouldCalculateForRealContent()
    {
        $request = RequestFactory::getInstance()->create(
            'PUT',
            'http://test.co/resource.xml?foo=bar&bar=foo',
            array(
                'Content-type' => "text/plain",
                'Date' => http_date()
            ),
            "helo\nworld"
        );
        $event = new Event(array(
            'request' => $request,
            'timestamp' => self::TIMESTAMP
        ));

        $p = new ApiAuthPlugin($this->config);
        $p->onRequestBeforeSend($event);

        $this->assertEquals(
            (string) $event['request']->getHeader('Content-MD5'),
            'MATnNnvfHYuh9MbanV26yg=='
        );
    }

    public function testMD5HeaderShouldLeaveTheContentMD5AloneIfProvided()
    {
        $request = $this->getRequest();
        $event = new Event(array(
            'request' => $request,
            'timestamp' => self::TIMESTAMP
        ));

        $p = new ApiAuthPlugin($this->config);
        $p->onRequestBeforeSend($event);

        $this->assertEquals(
            (string) $event['request']->getHeader('Content-MD5'),
            "1B2M2Y8AsgTpgAmY7PhCfg=="
        );
    }

    public function testShouldGenerateTheProperCanonicalString()
    {

        $request = $this->getRequest();

        $p = new ApiAuthPlugin($this->config);
        $canonicalString = $p->getCanonicalString($request);

        $this->assertEquals(self::CANONICAL_STRING, $canonicalString);
    }

    public function testGetHMACSignatureGeneratesAValidSignature()
    {
        $request = $this->getRequest();

        $p = new ApiAuthPlugin($this->config);
        $signature = $p->getHMACSignature($request);

        $this->assertEquals($signature, "pJU5sKxYnd1t83MxJLRsaUBqYSg=");
    }


    public function testShouldSignTheRequest()
    {
        $request = $this->getRequest();
        $event = new Event(array(
            'request' => $request,
            'timestamp' => self::TIMESTAMP
        ));

        $p = new ApiAuthPlugin($this->config);
        $signature = $p->getHMACSignature($request);
        $p->onRequestBeforeSend($event);

        $this->assertEquals(
            (string) $event['request']->getHeader('Authorization'),
            "APIAuth 1044:{$signature}"
        );
    }
}
