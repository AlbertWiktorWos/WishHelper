<?php

namespace App\MessageHandler;

use App\Message\UpdateCountriesMessage;
use App\Service\Item\CountryUpdater;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateCountriesMessageHandler
{
    public function __construct(
        private CountryUpdater $countryUpdater,
    ) {
    }

    public function __invoke(UpdateCountriesMessage $message): void
    {
        $this->countryUpdater->update($message->codes);
    }
}
