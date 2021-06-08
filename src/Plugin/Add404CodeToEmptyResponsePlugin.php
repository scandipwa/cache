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

        /*
         * In case if response returns object with empty data
         * set 404 code to response witch will prevent it from caching
         */
        if (
            empty(current($responseContent['data']))
            || empty(current(current($responseContent['data'])))
        ) {
            return $response->setStatusHeader(404);
        }

        return $response;
    }
}

