<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\FileHelper;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHelperTest extends TestCase
{
    public function testUploadSuccess(): void
    {
        $user = new User();

        // prepare Flysystem mock
        $filesystem = $this->createMock(FilesystemOperator::class);
        $logger = $this->createMock(LoggerInterface::class);


        // we expect writeStream to be called 1 time
        $filesystem->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->stringContains('avatars/'), // path ends in avatars
                $this->isType('resource'), // second argument is a resource (stream)
                $this->arrayHasKey('visibility')
            );

        $fileHelper = new FileHelper($filesystem, 'http://localhost', $logger);

        // create a temporary file
        $tmpFile = tmpfile();
        fwrite($tmpFile, base64_decode( // small picture in base64 to create a valid file
            'iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAIAAADZF8uwAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAkSURBVChTY/hPBBgKihjBAMqBASyKGBjQBWnnJqyAWor'
            .'+/wcA6sSidaC3FFMAAAAASUVORK5CYII='
        ));
        $path = stream_get_meta_data($tmpFile)['uri'];

        $uploadedFile = new UploadedFile(
            $path,
            'test.png',
            'image/png',
            null,
            true
        );

        $filename = $fileHelper->upload($user, $uploadedFile, 'avatars');

        $this->assertStringEndsWith('.png', $filename);

        fclose($tmpFile);
    }

    public function testUploadInvalidMime(): void
    {
        $user = new User();
        $filesystem = $this->createMock(FilesystemOperator::class);
        $logger = $this->createMock(LoggerInterface::class);

        $fileHelper = new FileHelper($filesystem, 'http://localhost', $logger);

        $tmpFile = tmpfile();
        fwrite($tmpFile, 'dummy content');
        $path = stream_get_meta_data($tmpFile)['uri'];

        $uploadedFile = new UploadedFile(
            $path,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid image type "text/plain". Allowed types: image/jpeg, image/png, image/webp, image/gif');

        $fileHelper->upload($user, $uploadedFile, 'avatars');

        fclose($tmpFile);
    }
}
