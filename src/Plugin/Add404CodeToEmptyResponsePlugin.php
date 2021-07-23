<?php
/**
 * ScandiPWA_Cache
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_Cache
 * @author      Aleksandrs Kondratjevs <Aleksandrs.Kondratjevs@scandiweb.com | info@scandiweb.com>
 * @copyright   Copyright (c) 2021 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Scandipwa\Cache\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Add404CodeToEmptyResponsePlugin
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * Add404CodeToEmptyResponsePlugin constructor.
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * @param InterceptorInterface $interceptor
     * @param HttpInterface $response
     * @param RequestInterface $request
     * @return HttpInterface
     */
    public function afterDispatch(InterceptorInterface $interceptor, HttpInterface $response, RequestInterface $request)
    {
        if (!array_key_exists('hash', $request->getParams())) {
            return $response;
        }

        $responseContent = $this->json->unserialize($response->getBody());

        if(key_exists('errors', $responseContent)) {
            return $this->set404Code($response);
        }

        if(!key_exists('data', $responseContent)) {
            return $response;
        }

        /*
         * In case if response returns object with empty data
         * set 404 code and adjust cache-control header
         * to response which will prevent it from caching
         */
        if (
            $this->validateResponseContent(current($responseContent['data']))
            || $this->validateResponseContent(current(current($responseContent['data'])))
        ) {
            return $this->set404Code($response);
        }

        return $response;
    }

    /**
     * Set 404 code and adjust cache-control header
     * @param HttpInterface $response
     */
    public function set404Code($response) {
        $response->setStatusHeader(404);
        $response->setHeader('cache-control', 'public, must-revalidate, proxy-revalidate, max-age=0', true);

        return $response;
    }

    /**
     * Check response content is it is null or array length is 0
     * @param $responeContent
     * @return bool|int|void
     */
    public function validateResponseContent($responseContent) {
        if (is_array($responseContent)) {
            return !count($responseContent);
        }

        return is_null($responseContent);
    }
}

