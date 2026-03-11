<?php

namespace App\Dto\Request;

use App\Service\Infrastructure\FileHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AvatarUpload
{
    #[Assert\NotNull(message: 'Avatar file is required')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: FileHelper::ALLOWED_MIME_TYPES,
        mimeTypesMessage: 'Only JPG, PNG and WEBP images are allowed'
    )]
    public ?UploadedFile $file = null;
}
