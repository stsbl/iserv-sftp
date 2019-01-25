<?php declare(strict_types = 1);

namespace Stsbl\SftpBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CoreBundle\Exception\TypeException;
use Stsbl\SftpBundle\Util\KeyConstraintFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
class AuthorizedKeyFile
{
    /**
     * @var string[]|ArrayCollection
     */
    private $keys;

    public function __construct()
    {
        $this->keys = new ArrayCollection();
    }

    /**
     * Return's the keys included in the key file.
     *
     * @return string[]|ArrayCollection
     */
    public function getKeys(): ArrayCollection
    {
        return $this->keys;
    }

    /**
     * Checks if lines are given in key file.
     */
    public function hasKeys(): bool
    {
        return !$this->keys->isEmpty();
    }

    /**
     * This fetches the key line from a certain position
     */
    public function fetchKey(int $position): ?string
    {
        return $this->keys->get($position);
    }

    public function countKeys(): int
    {
        return $this->keys->count();
    }

    /**
     * Adds the given string to the key file.
     *
     * @return $this
     */
    public function addKey(string $key): self
    {
        $this->keys->add($key);

        return $this;
    }

    /**
     * Removes the given string to the key file.
     *
     * @return $this
     */
    public function removeKey(string $key): self
    {
        $this->keys->removeElement($key);

        return $this;
    }

    /**
     * Validates keys inside array collection.
     *
     * @Assert\Callback()
     */
    public function validateKeys(ExecutionContextInterface $context): void
    {
        $constraints = [KeyConstraintFactory::getKeyFormatConstraint(), KeyConstraintFactory::getNotBlankConstraint()];

        foreach ($this->keys->getKeys() as $index) {
            $value = $this->fetchKey($index);

            // validate values manually and add constraint violation on proper position
            /** @var ConstraintViolationListInterface|ConstraintViolationInterface[] $violations */
            $violations = $context->getValidator()->validate($value, $constraints);

            foreach ($violations as $violation) {
                $context->buildViolation($violation->getMessage())
                    ->atPath(sprintf('keys[%d]', $index))
                    ->addViolation()
                ;
            }
        }
    }

    /**
     * @param string[] $lines
     */
    public static function createFromKeyArray(array $lines): self
    {
        $instance = new self();

        foreach ($lines as $line) {
            if (!is_string($line)) {
                throw TypeException::invalid($line, 'string', '$lines');
            }

            $instance->addKey($line);
        }

        return $instance;
    }
}
