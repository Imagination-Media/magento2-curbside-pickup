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
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action;
use Magento\Sales\Model\Order;

class View extends \Magento\Sales\Controller\AbstractController\View implements OrderInterface, HttpGetActionInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @param Registry $registry
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Registry $registry,
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $orderLoader, $resultPageFactory);
        $this->registry = $registry;
    }

    /**
     * Curbside Order view page
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof ResultInterface) {
            return $result;
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('curbside/order/history');
        }

        /** @var Order $order */
        $order = $this->getOrder();
        $resultPage->getConfig()->getTitle()->set(__('Curbside Order # %1', $order->getRealOrderId()));

        return $resultPage;
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    private function getOrder(): Order
    {
        return $this->registry->registry('current_order');
    }
}
