<?php

namespace App\Integration\RestIntegration\OpenRouter;

enum ResponseFormatTypes: string
{
    case MESSAGE_RESPONSE = 'message';
    case JSON_RESPONSE = 'json_object';
}
