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

namespace ImaginationMedia\CurbsidePickup\Model\Email;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilder;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;

class CurbsideOrderSender extends Sender
{
    public const ORDER_ACCEPTED_NOTIFICATION = 'order_accepted_notifier';
    public const ORDER_CUSTOMER_READY_NOTIFICATION = 'order_customer_ready_notifier';
    public const ORDER_PICKUP_READY_NOTIFICATION = 'order_pickup_ready_notifier';
    public const ORDER_PICKUP_REMINDER_NOTIFICATION = 'order_pickup_reminder_notifier';

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var string
     */
    private $emailTemplateId;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param Template $templateContainer
     * @param CurbsideOrderIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param string $emailTemplateId
     */
    public function __construct(
        Template $templateContainer,
        CurbsideOrderIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        ManagerInterface $eventManager,
        UrlInterface $url,
        string $emailTemplateId
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
        $this->emailTemplateId = $emailTemplateId;
        $this->url = $url;
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @param array $additionalData
     * @return bool
     */
    public function send(Order $order, array $additionalData = []): bool
    {
        $this->identityContainer->setTemplateId($this->getEmailTemplateId());
        $this->identityContainer->setStore($order->getStore());

        $transport = array_merge_recursive([
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'order_data' => [
                'order_url' => $this->getOrderUrl($order),
                'pickup_url' =>  $this->getPickupUrl($order),
                'customer_name' => $order->getCustomerName(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ]
        ], $additionalData);

        $transportObject = new DataObject($transport);
        $this->templateContainer->setTemplateVars($transportObject->getData());

        return $this->checkAndSend($order);
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @return bool
     */
    protected function checkAndSend(Order $order)
    {
        $notify = $this->identityContainer->getCopyMethod();
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        if ($notify) {
            $sender->send();
        } else {
            // Email copies are sent as separated emails if their copy method
            // is 'copy' or a customer should not be notified
            $sender->sendCopyTo();
        }

        return true;
    }

    /**
     * @return string
     */
    private function getEmailTemplateId(): string
    {
        return $this->emailTemplateId;
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getPickupUrl(Order $order): string
    {
        return $this->url->getUrl('curbside/order/pickup',  ['order_id' => $order->getEntityId()]);
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getOrderUrl(Order $order): string
    {
        return $this->url->getUrl('curbside/order/view', ['order_id' => $order->getEntityId()]);
    }
}
