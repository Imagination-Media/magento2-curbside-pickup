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

namespace ImaginationMedia\CurbsidePickup\Controller\Adminhtml\Order;

use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\Adminhtml\Order;
use Magento\Sales\Controller\OrderInterface;

class View extends Order implements OrderInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ImaginationMedia_CurbsidePickup::actions_view';

    /**
     * View order detail
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($order) {
            try {
                $resultPage = $this->_initAction();
                $resultPage->getConfig()->getTitle()->prepend(__('Curbside Orders'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Exception occurred during order load'));
                $resultRedirect->setPath('curbside/order/index');

                return $resultRedirect;
            }
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $order->getIncrementId()));
            return $resultPage;
        }
        $resultRedirect->setPath('curbside/*/');

        return $resultRedirect;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Page
     */
    protected function _initAction(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ImaginationMedia_CurbsidePickup::curbside_order');
        $resultPage->addBreadcrumb(__('Sales'), __('Curbside Orders'));
        $resultPage->addBreadcrumb(__('Orders'), __('Orders'));

        return $resultPage;
    }
}
