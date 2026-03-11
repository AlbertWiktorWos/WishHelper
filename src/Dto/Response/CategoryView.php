<?php

namespace App\Dto\Response;

use Symfony\Component\Serializer\Annotation\Groups;

class CategoryView
{
    #[Groups(['wish:read'])]
    public string $id;

    #[Groups(['wish:read'])]
    public string $name;

    #[Groups(['wish:read'])]
    public ?string $icon = null;
}
