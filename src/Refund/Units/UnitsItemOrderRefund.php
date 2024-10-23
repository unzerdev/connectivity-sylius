<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use SyliusUnzerPlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\RefundType;

final class UnitsItemOrderRefund implements UnitsItemOrderRefundInterface
{
    /** @var RepositoryInterface */
    private RepositoryInterface $refundUnitsRepository;

    public function __construct(RepositoryInterface $refundUnitsRepository)
    {
        $this->refundUnitsRepository = $refundUnitsRepository;
    }

    public function refund(OrderInterface $order, PartialRefundItems $partialRefundItems): array
    {
        $units = $order->getItemUnits();

        $unitsToRefund = [];

        /** @var OrderItemUnitInterface $unit */
        foreach ($units as $unit) {
            if (true === $this->hasUnitRefunded($order, $unit->getId())) {
                continue;
            }

            $partialItem = $partialRefundItems->findById($unit->getOrderItem()->getId());
            if (null !== $partialItem) {
                $unitsToRefund[] = new OrderItemUnitRefund(
                    $unit->getId(),
                    $unit->getTotal(),
                );

                $partialRefundItems->removeItem($partialItem);
            }
        }

        return $unitsToRefund;
    }

    public function getActualRefundedQuantity(OrderInterface $order, int $itemId): int
    {
        $allItems = array_filter($this->getActualRefunded($order, $itemId));

        return count($allItems);
    }

    private function getActualRefunded(OrderInterface $order, int $itemId): array
    {
        $units = $order->getItemUnits();

        $refundedUnits = [];
        foreach ($units as $unit) {
            if ($itemId === $unit->getOrderItem()->getId()) {
                $refundedUnits[] = $this->refundUnitsRepository->findOneBy([
                    'order' => $order->getId(),
                    'refundedUnitId' => $unit->getId(),
                    'type' => RefundType::orderItemUnit(),
                ]);
            }
        }

        return $refundedUnits;
    }

    private function hasUnitRefunded(OrderInterface $order, int $unitId): bool
    {
        $unitRefunded = $this->refundUnitsRepository->findOneBy([
            'order' => $order->getId(),
            'refundedUnitId' => $unitId,
            'type' => RefundType::orderItemUnit(),
        ]);

        return null !== $unitRefunded;
    }
}
