<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

use SyliusUnzerPlugin\Creator\OrderRefundCommandCreatorInterface;
use SyliusUnzerPlugin\Exceptions\InvalidRefundAmountException;
use SyliusUnzerPlugin\Logger\MollieLoggerActionInterface;
use Mollie\Api\Resources\Order;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class OrderRefund implements OrderRefundInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var OrderRefundCommandCreatorInterface */
    private $commandCreator;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        MessageBusInterface $commandBus,
        OrderRefundCommandCreatorInterface $commandCreator,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->commandBus = $commandBus;
        $this->commandCreator = $commandCreator;
        $this->loggerAction = $loggerAction;
    }

    public function refund(Order $order): void
    {
        try {
            $refundUnits = $this->commandCreator->fromOrder($order);
            $this->commandBus->dispatch($refundUnits);
        } catch (InvalidRefundAmountException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        } catch (HandlerFailedException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        }
    }
}
