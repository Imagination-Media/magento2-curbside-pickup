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
use Klarna\Core\Api\OrderRepositoryInterface;
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
     * PickupPost constructor.
     * @param CurbsideOrderInterface $curbsideOrderService
     * @param OrderRepositoryInterface $orderRepository
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        CurbsideOrderInterface $curbsideOrderService,
        OrderRepositoryInterface $orderRepository,
        Validator $formKeyValidator,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->curbsideOrderService = $curbsideOrderService;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        $orderId = $postData['order_id'];
        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('No Id provided for load.'));
        }

        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey && $this->getRequest()->isPost()) {
            try {
                /** @var OrderInterface $order */
                $order = $this->orderRepository->getById($orderId);
                $order = $this->curbsideOrderService->saveCurbsideData($order, $postData);
                $this->curbsideOrderService->updateStatus(
                    OrderStatus::STATUS_CUSTOMER_READY,
                    $order,
                    'Customer ready to pickup order'
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getTraceAsString());
                $this->messageManager->addExceptionMessage($e, __('We can\'t save the Curbside Data.'));
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit/order_id/' . $orderId);
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
}
