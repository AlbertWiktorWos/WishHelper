<?php

namespace App\Dto\Response;

use Symfony\Component\Serializer\Annotation\Groups;

class TagView
{
    #[Groups(['wish:read'])]
    public string $id;

    #[Groups(['wish:read'])]
    public string $name;
}
