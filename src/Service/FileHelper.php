<?php

namespace App\Service;

use App\Entity\User;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;

class FileHelper
{
    public const AVATAR_DIRECTORY = 'avatars';
    public const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(
        private FilesystemOperator $publicStorage,
        private string $publicBaseUrl,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Upload a file to the given directory for the user.
     * Throws exception if MIME type is invalid or file cannot be written.
     */
    public function upload(User $user, File $file, string $directory = self::AVATAR_DIRECTORY): string
    {
        $mime = $file->getMimeType();

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            $this->logger->warning('Invalid file type for user avatar.', [
                'user_id' => $user->getId(),
                'mime' => $mime,
                'filename' => $file->getFilename(),
            ]);
            throw new \RuntimeException(sprintf('Invalid image type "%s". Allowed types: %s', $mime, implode(', ', self::ALLOWED_MIME_TYPES)));
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new \RuntimeException('Could not determine file extension.'),
        };

        // generate unique filename
        $filename = Uuid::v4()->toRfc4122().'.'.$extension;

        $stream = fopen($file->getPathname(), 'r');
        try {
            $this->publicStorage->writeStream(
                $directory.'/'.$filename,
                $stream,
                ['visibility' => 'public']
            );
        } catch (UnableToWriteFile $e) {
            $this->logger->error('Failed to write file to storage.', [
                'user_id' => $user->getId(),
                'filename' => $filename,
                'exception' => $e,
            ]);
            throw new \RuntimeException('Could not upload file.', 0, $e);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        // delete previous avatar if it exists
        if ($user->getAvatar()) {
            try {
                $this->publicStorage->delete($directory.'/'.$user->getAvatar());
            } catch (UnableToDeleteFile $e) {
                $this->logger->warning('Failed to delete old avatar.', [
                    'user_id' => $user->getId(),
                    'filename' => $user->getAvatar(),
                    'exception' => $e,
                ]);
            }
        }

        return $filename;
    }

    /**
     * Return public URL for a file.
     */
    public function getPublicUrl(string $filename, string $directory = self::AVATAR_DIRECTORY): string
    {
        return rtrim($this->publicBaseUrl, '/').'/'.$directory.'/'.$filename;
    }
}
