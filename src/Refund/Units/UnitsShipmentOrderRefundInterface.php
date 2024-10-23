<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;

interface UnitsShipmentOrderRefundInterface
{
    public function refund(Order $order, OrderInterface $syliusOrder): array;
}
