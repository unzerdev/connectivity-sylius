<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class PartialShipItems
{
    /** @var Collection<int, PartialShipItem> */
    private Collection $partialShipItems;

    public function __construct()
    {
        $this->partialShipItems = new ArrayCollection();
    }

    public function setPartialShipItem(PartialShipItem $partialShipItem): void
    {
        $partialItem = $this->findPartialShipItemWhereItem($partialShipItem);

        if (null !== $partialItem) {
            $partialItem->setQuantity($partialItem->getQuantity() + 1);

            return;
        }

        $this->partialShipItems->add($partialShipItem);
    }

    /**
     * @return Collection<int, PartialShipItem>
     */
    public function getPartialShipItems(): Collection
    {
        return $this->partialShipItems;
    }

    public function getArrayFromObject(): array
    {
        $data = [];
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            $data[] = [
                'id' => $item->getLineId(),
                'quantity' => $item->getQuantity(),
            ];
        }

        return $data;
    }

    public function findPartialShipItemWhereItem(PartialShipItem $partialShipItem): ?PartialShipItem
    {
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            if ($item->getId() === $partialShipItem->getId()) {
                return $item;
            }
        }

        return null;
    }

    public function findById(int $id): ?PartialShipItem
    {
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }
}
