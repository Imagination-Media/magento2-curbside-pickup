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

use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use ImaginationMedia\CurbsidePickup\Helper\Data as CurbsideHelper;
use Psr\Log\LoggerInterface;

class PickupPost extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CurbsideOrderInterface
     */
    private CurbsideOrderInterface $curbsideOrderService;

    /**
     * @var CurbsideHelper
     */
    private CurbsideHelper $curbsideHelper;

    /**
     * PickupPost constructor.
     * @param CurbsideOrderInterface $curbsideOrderService
     * @param OrderRepositoryInterface $orderRepository
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     * @param CurbsideHelper $curbsideHelper
     * @param Context $context
     */
    public function __construct(
        CurbsideOrderInterface $curbsideOrderService,
        OrderRepositoryInterface $orderRepository,
        Validator $formKeyValidator,
        LoggerInterface $logger,
        CurbsideHelper $curbsideHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->curbsideOrderService = $curbsideOrderService;
        $this->curbsideHelper = $curbsideHelper;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $orderId = $postData['order_id'];
        $token = $postData['token'];
        if (!$orderId && !$token) {
            $this->messageManager->addErrorMessage(__('Action denied.'));
        }

        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey && $this->getRequest()->isPost()) {
            try {

                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($orderId);
                $order = $this->curbsideOrderService->saveCurbsideData($order, $postData);

                if ($this->isCustomerReadyForPickup($order, $postData)) {
                    $this->curbsideOrderService->updateStatus(
                        OrderStatus::STATUS_CUSTOMER_READY,
                        $order,
                        'Customer ready to pickup order'
                    );
                    $this->messageManager->addSuccessMessage(__('Pick up Info has been saved successfully. Order ready for delivery.'));
                } elseif ($this->curbsideHelper->isScheduledPickupEnabled($order->getStoreId())) {
                    $this->messageManager->addSuccessMessage(
                        __('Pick up has been scheduled successfully. Please inform us once when on location for Pick Up.')
                    );
                }

            } catch (\Exception $e) {
                $this->logger->critical($e->getTraceAsString());
                $this->messageManager->addExceptionMessage($e, __('We can\'t save the Curbside Data.'));
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/view/order_id/' . $orderId);
        return $resultRedirect;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {

        $orderId = $this->getRequest()->getParam('order_id');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit/order_id/' . $orderId);

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * @param OrderInterface $order
     * @param array $postData
     * @return bool
     */
    private function isCustomerReadyForPickup(OrderInterface $order, array $postData): bool
    {
        if (!$this->curbsideHelper->isScheduledPickupEnabled($order->getStoreId())) {
            return true;
        }
        return isset($postData['scheduled_pickup']) && $postData['scheduled_pickup'] === 'Yes';
    }
}
