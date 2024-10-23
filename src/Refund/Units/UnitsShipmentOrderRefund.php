<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use SyliusUnzerPlugin\Helper\ConvertOrderInterface;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Model\RefundType;
use Sylius\RefundPlugin\Model\ShipmentRefund;

final class UnitsShipmentOrderRefund implements UnitsShipmentOrderRefundInterface
{
    /** @var RepositoryInterface */
    private $refundUnitsRepository;

    public function __construct(RepositoryInterface $refundUnitsRepository)
    {
        $this->refundUnitsRepository = $refundUnitsRepository;
    }

    public function refund(Order $order, OrderInterface $syliusOrder): array
    {
        if ($this->hasShipmentRefund($syliusOrder)) {
            return [];
        }

        /** @var mixed $line */
        foreach ($order->lines as $line) {
            if (!property_exists($line, 'type') ||
                !property_exists($line, 'refundableQuantity')) {
                throw new \InvalidArgumentException();
            }

            if (ConvertOrderInterface::SHIPPING_TYPE === $line->type && 0 < $line->quantityRefunded) {
                /** @var Adjustment $refundedShipment */
                $refundedShipment = $syliusOrder->getAdjustments('shipping')->first();

                return [
                    new ShipmentRefund(
                        $refundedShipment->getId(),
                        $refundedShipment->getAmount()
                    ),
                ];
            }
        }

        return [];
    }

    private function hasShipmentRefund(OrderInterface $order): bool
    {
        $unitRefunded = $this->refundUnitsRepository->findOneBy([
            'order' => $order->getId(),
            'type' => RefundType::shipment(),
        ]);

        return null !== $unitRefunded;
    }
}
