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

namespace ImaginationMedia\CurbsidePickup\Model\Action;

use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Exception\CouldNotInvoiceException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Shipping\Model\ShipmentNotifier;
use Psr\Log\LoggerInterface;
use ImaginationMedia\CurbsidePickup\Model\CurbsideOrder as CurbsideOrderModel;
use Magento\Framework\Serialize\SerializerInterface;

class CurbsideOrder implements CurbsideOrderInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentNotifier
     */
    private $shipmentNotifier;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var CurbsideOrderModel
     */
    private $curbsideOrderModel;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @param CurbsideOrderModel $curbsideOrderModel
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceSender $invoiceSender
     * @param ShipmentNotifier $shipmentNotifier
     * @param SerializerInterface $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurbsideOrderModel $curbsideOrderModel,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceSender $invoiceSender,
        ShipmentNotifier $shipmentNotifier,
        SerializerInterface $json,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->shipmentRepository = $shipmentRepository;
        $this->logger = $logger;
        $this->invoiceSender = $invoiceSender;
        $this->curbsideOrderModel = $curbsideOrderModel;
        $this->json = $json;
    }

    /**
     * @param OrderInterface $order
     * @return null|InvoiceInterface
     * @throws \Exception
     */
    public function doInvoice(OrderInterface $order): ?InvoiceInterface
    {
        try {
            if (!$order->canInvoice()) {
                throw new CouldNotInvoiceException(__('No right conditions met to invoice.'));
            }

            /** @var Invoice $invoice */
            $invoice = $this->curbsideOrderModel->invoiceOrder($order);
            $this->invoiceSender->send($invoice);
            return $invoice;
        } catch (CouldNotInvoiceException $e) {
            $this->logger->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     * @return null|ShipmentInterface
     * @throws LocalizedException
     */
    public function doShipment(OrderInterface $order): ?ShipmentInterface
    {
        try {
            $order = $this->orderRepository->get($order->getEntityId());
            if (!$order->canShip()) {
                throw new LocalizedException(__('You can\'t create the Shipment for this order.'));
            }
            /** @var Order\Shipment $shipment */
            $shipment = $this->curbsideOrderModel->shipOrder($order);

            $this->orderRepository->save($shipment->getOrder());
            $this->shipmentNotifier->notify($shipment);
            return $this->shipmentRepository->save($shipment);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }

        return null;
    }

    /**
     * @param string $status
     * @param OrderInterface $order
     * @param string|null $comment
     * @return null|OrderInterface
     */
    public function updateStatus(
        string $status,
        OrderInterface $order,
        ?string $comment = null
    ): ?OrderInterface
    {
        try {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($status);

            if ($comment !== null) {
                $order->addStatusToHistory($order->getStatus(), $comment);
            }

            /** @var Order $order */
            $order = $this->orderRepository->save($order);
            return $order;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     * @param array $data
     * @return null|OrderInterface
     * @throws LocalizedException
     */
    public function saveCurbsideData(OrderInterface $order, array $data): ?OrderInterface
    {
        try {
            if (isset($data['curbside_delivery_time'])) {
                $deliveryTime = new \DateTime($data['curbside_delivery_time']);
                $order->setCurbsideDeliveryTime($deliveryTime->format('Y-m-d H:i:s'));
            }
            $existingData = $this->json->unserialize($order->getCurbsideData());
            foreach ($data as $key => $value) {
                if ($value !== null && $value !== '' && in_array($key, [
                        'car_model',
                        'car_plate',
                        'car_color',
                        'note',
                        'pickup_location_name',
                        'parking_spot',
                        'scheduled_pickup'
                    ])
                ) {
                    $existingData[$key]= $value;
                }
            }
            $dataJson = $this->json->serialize($existingData);
            $order->setCurbsideData($dataJson);

            /** @var Order $order */
            $order = $this->orderRepository->save($order);
            return $order;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }
}
