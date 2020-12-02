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

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\Order;

class CurbsidePickupTokenManagement implements CurbsidePickupTokenManagementInterface
{
    const PREFIX = 'x#/&/#$?\=*"!';

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Random
     */
    private Random $mathRandom;

    /**
     * CurbsidePickupTokenManagement constructor.
     *
     * @param EncryptorInterface $encryptor
     * @param Random $mathRandom
     */
    public function __construct(EncryptorInterface $encryptor, Random $mathRandom)
    {
        $this->encryptor = $encryptor;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function generateToken(): string
    {
        $random = $this->mathRandom->getUniqueHash(self::PREFIX);
        return $this->encryptor->hash($random);
    }

    /**
     * @param string $token
     * @param Order $order
     * @return bool
     */
    public function isValidPickupTokenForOrder(string $token, Order $order): bool
    {
        return $order->getCurbsidePickupToken() === $token;
    }
}
