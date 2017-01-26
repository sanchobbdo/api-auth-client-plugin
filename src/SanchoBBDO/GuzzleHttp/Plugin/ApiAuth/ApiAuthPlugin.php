<?php

/**
 * ApiAuthPlugin source.
 *
 * PHP Version 5
 *
 * @category GuzzlePlugin
 * @package  GuzzlePlugin
 * @author   Camilo Aguilar <camiloaguilar@sanchobbdo.com.co>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://
 * @filesource
 */

namespace SanchoBBDO\GuzzleHttp\Plugin\ApiAuth;

use \GuzzleHttp\Collection;
use \GuzzleHttp\Event\BeforeEvent;
use \GuzzleHttp\Event\RequestEvents;
use \GuzzleHttp\Event\SubscriberInterface;
use \GuzzleHttp\Message\RequestInteface;
use \GuzzleHttp\Stream\StreamInterface;


/**
 * Api Auth signing plugin.
 *
 * @category GuzzlePlugin
 * @package  GuzzlePlugin
 * @author   Camilo Aguilar <camiloaguilar@sanchobbdo.com.co>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://
 */
class ApiAuthPlugin implements SubscriberInterface
{
    /**
     * @var Collection Configuration settings.
     */
    protected $config;

    /**
     * Create a new Api Auth plugin.
     *
     * @param array $config Configuration array containing these parameters:
     *     - string 'accessId'      Access ID
     *     - string 'secretKey'     Secret Key
     */
    public function __construct($config)
    {
        $defaults = array('accessId' => '', 'secretKey' => '');
        $required = array('accessId', 'secretKey');
        $this->config = Collection::fromConfig($config, $defaults, $required);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return array(
            'before' => array('onBefore', \GuzzleHttp\Event\RequestEvents::SIGN_REQUEST)
        );
    }

    /**
     * Request before handler.
     *
     * @param Event $event Event received.
     *
     * @return null
     */
    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();

        $this->setMD5HeaderOnRequest($request);
        $this->setDateHeaderOnRequest($request);
        $this->setAuthorizationHeaderOnRequest($request);
    }

    /**
     * Writes Content-MD5 header on given rquest.
     *
     * @param RequestInteface $request Request to write header to.
     *
     * @return null
     */
    protected function setMD5HeaderOnRequest($request)
    {
        if (!$contentMD5 = $request->getHeader('Content-MD5')) {
            $body = $this->getRequestBody($request);
            $request->setHeader('Content-MD5', $this->calculateMD5($body));
        }
    }

    /**
     * Returns the request's body.
     *
     * @param RequestInterface $request Request to get body from.
     *
     * @return string
     */
    protected function getRequestBody($request)
    {
        return (string) $request->getBody();
    }

    /**
     * Calculates content's MD5 from body.
     *
     * @param string $body Request's content.
     *
     * @return string
     */
    protected function calculateMD5($body)
    {
        return base64_encode(md5($body, true));
    }

    /**
     * Sets the current date in the request header if not present.
     *
     * @param RequestInterface $request Request to write header to.
     *
     * @return null
     */
    protected function setDateHeaderOnRequest($request)
    {
        if (!$date = $request->getHeader('Date')) {
            $request->setHeader('Date', http_date());
        }
    }

    /**
     * Builds canonical string from request.
     *
     * @param RequestInterface $request Request to get canonical string from.
     *
     * @return string
     */
    public function getCanonicalString($request)
    {
        $parts = array(
            $request->getHeader('content-type'),
            $request->getHeader('content-md5'),
            $request->getResource(),
            $request->getHeader('date')
        );

        return join(',', $parts);
    }

    /**
     * Generates a HMAC signature from the request and the secret key.
     *
     * @param RequestInterface $request Request to generate signature from.
     *
     * @return string
     */
    public function getHMACSignature($request)
    {
        $canonicalString = $this->getCanonicalString($request);
        $s = hash_hmac('sha1', $canonicalString, $this->config['secretKey'], true);
        $s = base64_encode($s);
        $s = trim($s);
        return $s;
    }

    /**
     * Adds an Authorization header to the request.
     *
     * @param RequestInterface $request Request to sign.
     *
     * @return null
     */
    public function setAuthorizationHeaderOnRequest($request)
    {
        $signature = $this->getHMACSignature($request);
        $authorized_header = "APIAuth {$this->config['accessId']}:{$signature}";
        $request->setHeader('Authorization', $authorized_header);
    }
}
