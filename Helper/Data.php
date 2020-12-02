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

namespace ImaginationMedia\CurbsidePickup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * Configuration paths
     */
    private const XML_IS_CURBSIDE_PICKUP = 'carriers/instore/curbside/active';
    private const XML_IS_SCHEDULED_PICKUP = 'carriers/instore_curbside/scheduledpickup';
    private const XML_PICKUP_THRESHOLD = 'carriers/instore_curbside/threshold';

    /**
     * Email Templates
     */
    public const XML_PATH_IS_EMAIL_NOTIFICATION_ENABLED = 'sales_email/curbside_order/enabled';
    public const XML_PATH_EMAIL_ACCEPT_TEMPLATE = 'sales_email/curbside_order/accept_template';
    public const XML_PATH_EMAIL_READY_TEMPLATE = 'sales_email/curbside_order/ready_template';
    public const XML_PATH_EMAIL_DELIVERY_REMINDER_TEMPLATE = 'sales_email/curbside_order/delivery_reminder_template';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(StoreManagerInterface $storeManager, Context $context)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @param null|mixed $store
     * @return bool
     */
    public function isScheduledPickupEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_IS_SCHEDULED_PICKUP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null|mixed $store
     * @return bool
     */
    public function isEmailNotificationsEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_EMAIL_NOTIFICATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|mixed $store
     * @return bool
     */
    public function isCurbsidePickupEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_IS_CURBSIDE_PICKUP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|mixed $store
     * @return string
     */
    public function getPickupThreshold($store = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PICKUP_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Order Accepteed template id
     *
     * @return mixed
     */
    public function getAcceptTemplateId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_ACCEPT_TEMPLATE);
    }

    /**
     * Order Ready template id
     *
     * @return mixed
     */
    public function getReadyTemplateId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_READY_TEMPLATE);
    }

    /**
     * Delivery reminder template id
     *
     * @return mixed
     */
    public function getDeliveryReminderTemplateId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_DELIVERY_REMINDER_TEMPLATE);
    }

    /**
     * @param string $dateField
     * @param string $format
     * @param null|int $storeId
     * @return string
     */
    public function getFormattedDate(string $dateField, $format = 'Y-m-d', ?int $storeId = null): string
    {
        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateField);
        $timezone = $this->scopeConfig->getValue(
            'general/locale/timezone',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($timezone) {
            $storeTime = new \DateTimeZone($timezone);
            $datetime->setTimezone($storeTime);
        }

        return $datetime->format($format);
    }

    /**
     * Return store
     *
     * @return StoreInterface
     */
    private function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
