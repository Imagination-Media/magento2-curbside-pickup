<?php
/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Antonio Lolić <antonio@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Controller\OrderInterface;

class Pickup extends \Magento\Sales\Controller\AbstractController\View implements OrderInterface, HttpGetActionInterface
{
    /**
     * Curbside Pickup view page
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $orderId = $this->getRequest()->getParam('order_id');

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('curbside_pickup_form');
        if ($block || !$orderId) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('Curbside Pickup # %1', $orderId));

        return $resultPage;
    }
}
