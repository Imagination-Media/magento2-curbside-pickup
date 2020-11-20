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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Modal extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'ImaginationMedia_CurbsidePickup::modal_view';

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var HttpFactory
     */
    private HttpFactory $httpFactory;

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Modal constructor.
     *
     * @param Registry $registry
     * @param HttpFactory $httpFactory
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        HttpFactory $httpFactory,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->httpFactory = $httpFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('No Id provided for load.'));
            return $this->_redirect('*/*');
        }

        try {
            $order = $this->orderRepository->get($id);
            $page = $this->resultPageFactory->create()
                ->getLayout()
                ->getBlock('curbsidepickup.modal');

        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('Error occurred while loading in modal. Please try again later.')
            );
            return $this->_redirect('*/*');
        }

        $this->registry->register('order', $order);
        $this->registry->register('sales_order', $order);

        /** @var Http $resultHttp */
        $resultHttp = $this->httpFactory->create();
        return $resultHttp->setContent($page->toHtml());
    }
}
