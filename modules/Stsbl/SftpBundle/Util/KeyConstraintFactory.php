<?php declare(strict_types = 1);

namespace Stsbl\SftpBundle\Util;

/*
 * The MIT License
 *
 * Copyright 2019 Felix Jacobi.
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

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class KeyConstraintFactory
{
    private const SSH_PUBLIC_KEY_REGEX = '/^ssh-rsa\s|^$/';

    /**
     * @var Regex
     */
    private static $keyFormat;

    /**
     * @var NotBlank
     */
    private static $notBlank;

    public static function getKeyFormatConstraint(): Regex
    {
        if (null === self::$keyFormat) {
            self::$keyFormat = new Regex([
                'pattern' => self::SSH_PUBLIC_KEY_REGEX,
                'htmlPattern' => self::SSH_PUBLIC_KEY_REGEX,
                'message' => _('You must enter a valid public key. Did you may enter a private key?')
            ]);
        }

        return self::$keyFormat;
    }

    public static function getNotBlankConstraint(): NotBlank
    {
        if (null === self::$notBlank) {
            self::$notBlank = new NotBlank(['message' => _('You must enter a key.')]);
        }

        return self::$notBlank;
    }
}
