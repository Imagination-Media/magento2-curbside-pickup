<?php

/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @author Antonio Lolić <antonio@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Class OrderStatus
 * @package ImaginationMedia\CurbsidePickup\Setup\Patch\Data
 */
class OrderStatus implements DataPatchInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_ACCEPTED       = 'curbside_accepted';
    public const STATUS_ACCEPTED_LABEL = 'Order In Progress';
    public const STATUS_ACCEPTED_GRID_ACTION_LABEL = 'Accept';

    public const STATUS_READY_TO_PICK_UP       = 'curbside_ready';
    public const STATUS_READY_TO_PICK_UP_LABEL = 'Ready to be picked up';
    public const STATUS_READY_TO_PICK_UP_GRID_ACTION_LABEL = 'Mark As Ready';

    public const STATUS_CUSTOMER_READY       = 'curbside_customer_ready';
    public const STATUS_CUSTOMER_READY_LABEL = 'Customer is ready to pick-up';
    public const STATUS_CUSTOMER_READY_GRID_ACTION_LABEL = 'Deliver';

    public const STATUS_COMPLETE_LABEL = 'Completed';
    public const STATUS_WAITING_USER_LABEL = 'Waiting for user...';

    public const STATUS_PENDING = 'pending';

    /**
     * @var StatusFactory
     */
    protected StatusFactory $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected StatusResourceFactory $statusResourceFactory;

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $connection;

    /**
     * OrderStatus constructor.
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->addStatus(
            self::STATUS_ACCEPTED,
            self::STATUS_ACCEPTED_LABEL,
            "processing"
        );

        $this->addStatus(
            self::STATUS_READY_TO_PICK_UP,
            self::STATUS_READY_TO_PICK_UP_LABEL,
            "processing"
        );

        $this->addStatus(
            self::STATUS_CUSTOMER_READY,
            self::STATUS_CUSTOMER_READY_LABEL,
            "processing"
        );
    }

    /**
     * Add new status
     *
     * @param string $code
     * @param string $label
     * @param string $state
     * @throws \Exception
     */
    protected function addStatus(string $code, string $label, string $state): void
    {
        /**
         * Create status
         *
         * @var $statusResource StatusResource
         * @var $status Status
         */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => $code,
            'label'  => $label,
        ]);
        try {
            $statusResource->save($status);

            /**
             * Assign status to state
             */
            $this->connection->insert(
                $this->connection->getTableName("sales_order_status_state"),
                [
                    "status"           => $code,
                    "state"            => $state,
                    "is_default"       => 0,
                    "visible_on_front" => 1
                ]
            );
        } catch (AlreadyExistsException $exception) {
            return;
        }
    }
}
