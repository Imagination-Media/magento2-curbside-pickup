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

namespace ImaginationMedia\CurbsidePickup\ViewModel\User;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use ImaginationMedia\CurbsidePickup\Model\User\TimeZoneSession;

class CurrentTimezone implements ArgumentInterface
{
    /**
     * @var TimeZoneSession
     */
    private $timeZoneSession;

    /**
     * CurrentTimezone constructor.
     * @param TimeZoneSession $timeZoneSession
     */
    public function __construct(TimeZoneSession $timeZoneSession)
    {
        $this->timeZoneSession = $timeZoneSession;
    }

    /**
     * @return string|null
     */
    public function getTimezoneOffset(): ?string
    {
        return $this->timeZoneSession->getTimeZoneOffset();
    }

    /**
     * @param string $timezoneOffset
     */
    public function saveTimezoneOffsetInSession(string $timezoneOffset)
    {
        $this->timeZoneSession->setTimeZoneOffset($timezoneOffset);
    }
}

