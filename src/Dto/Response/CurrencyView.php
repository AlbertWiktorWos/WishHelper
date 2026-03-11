<?php

namespace App\Dto\Response;

use Symfony\Component\Serializer\Annotation\Groups;

class CurrencyView
{
    #[Groups(['wish:read'])]
    public string $id;

    #[Groups(['wish:read'])]
    public string $code;

    #[Groups(['wish:read'])]
    public string $name;
}
