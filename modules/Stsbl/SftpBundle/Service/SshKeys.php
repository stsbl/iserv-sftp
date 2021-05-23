<?php

declare(strict_types=1);

namespace Stsbl\SftpBundle\Service;

use IServ\FileBundle\Filesystem\Local;
use IServ\FilesystemBundle\Exception\FilesystemNotFoundException;
use IServ\FilesystemBundle\Service\Filesystem;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Stsbl\SftpBundle\Model\AuthorizedKeysFile;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class SshKeys
{
    private const AUTHORIZED_KEY_PATH = '.ssh/authorized_keys';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get existing public keys from User's home.
     *
     * @return string[] List of public RSA keys
     */
    private function fetchPublicKeysFromAuthorizedKeys(): array
    {
        $keys = [];

        try {
            $local = $this->filesystem->getFilesystem(Local::getAdapterSource());
        } catch (FilesystemNotFoundException $e) {
            throw new \LogicException('Cannot happen!', 0, $e);
        }

        if ($local->has(self::AUTHORIZED_KEY_PATH)) {
            $file = $local->get(self::AUTHORIZED_KEY_PATH);
            if (false === $content = $file->read()) {
                throw new \RuntimeException('Could not read authorized keys file!');
            }

            $keys = explode("\n", $content);

            // remove empty lines
            foreach ($keys as $index => $line) {
                if ('' === $line) {
                    unset($keys[$index]);
                }
            }
        }

        return $keys;
    }

    /**
     * Returns the model to edit the user's authorized keys file.
     */
    public function fetchPublicKeys(): AuthorizedKeysFile
    {
        return AuthorizedKeysFile::createFromKeyArray($this->fetchPublicKeysFromAuthorizedKeys());
    }

    /**
     * This writes back the model to the user's authorized keys file.
     */
    public function putPublicKeys(AuthorizedKeysFile $file): void
    {
        try {
            $local = $this->filesystem->getFilesystem(Local::getAdapterSource());
        } catch (FilesystemNotFoundException $e) {
            throw new \LogicException('Cannot happen!', 0, $e);
        }

        $dir = dirname(self::AUTHORIZED_KEY_PATH);

        if (!$local->has($dir)) {
            $local->createDir($dir);
        }

        try {
            $local->write(self::AUTHORIZED_KEY_PATH, implode("\n", $file->getKeys()->toArray()));
        } catch (FileExistsException $e) {
            try {
                $local->update(self::AUTHORIZED_KEY_PATH, implode("\n", $file->getKeys()->toArray()));
            } catch (FileNotFoundException $e) {
                throw new \LogicException('Cannot happen!', 0, $e);
            }
        }
    }
}
