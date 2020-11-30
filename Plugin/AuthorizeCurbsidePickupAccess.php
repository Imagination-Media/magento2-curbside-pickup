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

namespace ImaginationMedia\CurbsidePickup\Plugin;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ActionInterface;
use ImaginationMedia\CurbsidePickup\Model\ResourceModel\CurbsideOrder as CurbsideOrderResource;
use Magento\Framework\Exception\LocalizedException;

class AuthorizeCurbsidePickupAccess
{
    /**
     * @var Url
     */
    protected Url $customerUrl;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var CurbsideOrderResource
     */
    protected CurbsideOrderResource $curbsideOrderResource;

    /**
     * @param CurbsideOrderResource $curbsideOrderResource
     * @param Url $customerUrl
     * @param Session $customerSession
     */
    public function __construct(
        CurbsideOrderResource $curbsideOrderResource,
        Url $customerUrl,
        Session $customerSession
    ) {
        $this->customerUrl = $customerUrl;
        $this->customerSession = $customerSession;
        $this->curbsideOrderResource = $curbsideOrderResource;
    }

    /**
     * Authenticate user
     *
     * @param ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(ActionInterface $subject, RequestInterface $request)
    {
        if (!$this->isTokenExists($request)) {
            $loginUrl = $this->customerUrl->getLoginUrl();
            if (!$this->customerSession->authenticate($loginUrl) && !$this->isTokenExists($request)) {
                $subject->getActionFlag()->set('', $subject::FLAG_NO_DISPATCH, true);
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws LocalizedException
     */
    private function isTokenExists(RequestInterface $request): bool
    {
        $token = $request->getParam('token');
        return $token !== null
            ? $this->curbsideOrderResource->doesPickUpTokenExists($token)
            : false;
    }
}
