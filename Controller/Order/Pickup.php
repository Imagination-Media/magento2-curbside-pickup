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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\OrderInterface;

class Pickup extends Action implements OrderInterface, HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Curbside Pickup view page
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $token = $this->getRequest()->getParam('token');

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('curbside_pickup_form');
        if ($block || !$orderId || $token) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('Curbside Pickup # %1', $orderId));

        return $resultPage;
    }
}
