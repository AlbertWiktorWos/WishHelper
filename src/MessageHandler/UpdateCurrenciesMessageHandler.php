<?php

namespace App\MessageHandler;

use App\Message\UpdateCurrenciesMessage;
use App\Service\Item\CurrencyUpdater;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateCurrenciesMessageHandler
{
    public function __construct(
        private CurrencyUpdater $currencyUpdater,
    ) {
    }

    public function __invoke(UpdateCurrenciesMessage $message): void
    {
        $this->currencyUpdater->update($message->codes);
    }
}
