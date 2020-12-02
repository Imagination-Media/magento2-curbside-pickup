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

namespace ImaginationMedia\CurbsidePickup\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Convert\Order as OrderConverter;

class CurbsideOrder
{
    public const FIELD_CURBSIDE               = "curbside";
    public const FIELD_CURBSIDE_DATA          = "curbside_data";
    public const FIELD_CURBSIDE_DELIVERY_TIME = "curbside_delivery_time";
    public const FIELD_CURBSIDE_PICKUP_TOKEN = "curbside_pickup_token";

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var OrderConverter
     */
    private $orderConverter;

    /**
     * CurbsideOrder constructor.
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param OrderConverter $orderConverter
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        OrderConverter $orderConverter
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->orderConverter = $orderConverter;
    }

    /**
     * @param OrderInterface $order
     * @return InvoiceInterface
     * @throws LocalizedException
     */
    public function invoiceOrder(OrderInterface $order): InvoiceInterface
    {
        $invoice = $this->invoiceService->prepareInvoice($order);
        if (!$invoice) {
            throw new LocalizedException(__('We can\'t save the invoice right now.'));
        }
        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $this->invoiceRepository->save($invoice);

        $transactionSave = $this->transactionFactory->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();

        return $invoice;
    }

    /**
     * @param OrderInterface $order
     * @return ShipmentInterface
     * @throws LocalizedException
     */
    public function shipOrder(OrderInterface $order): ShipmentInterface
    {
        /** @var Order\Shipment $orderShipment */
        $orderShipment = $this->orderConverter->toShipment($order);
        $this->processOrderItems($order, $orderShipment);

        $orderShipment->register();
        $orderShipment->getOrder()->setIsInProcess(true);

        return $orderShipment;
    }

    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $orderShipment
     */
    private function processOrderItems(OrderInterface $order, ShipmentInterface $shipment): void
    {
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qty);
            $shipment->addItem($shipmentItem);
        }
    }
}
