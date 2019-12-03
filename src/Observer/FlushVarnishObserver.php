<?php


namespace ScandiPWA\Cache\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ScandiPWA\PersistedQuery\Model\PurgeCache;

class FlushVarnishObserver implements ObserverInterface
{
    /**
     * @var PurgeCache
     */
    private $purgeCache;
    
    /**
     * FlushVarnishObserver constructor.
     * @param PurgeCache $purgeCache
     */
    public function __construct(PurgeCache $purgeCache)
    {
        $this->purgeCache = $purgeCache;
    }
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $identities= $observer->getEntity()->getIdentities();
        $this->purgeCache->sendPurgeRequest(implode(',', $identities));
    }
}
