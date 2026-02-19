<?php

namespace App\Dto;

use App\Service\FileHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AvatarUploadDTO
{
    #[Assert\NotNull(message: 'Avatar file is required')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: FileHelper::ALLOWED_MIME_TYPES,
        mimeTypesMessage: 'Only JPG, PNG and WEBP images are allowed'
    )]
    public ?UploadedFile $file = null;
}
