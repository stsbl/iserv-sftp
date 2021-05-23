<?php

declare(strict_types=1);

namespace Stsbl\SftpBundle;

use IServ\CoreBundle\Routing\AutoloadRoutingBundleInterface;
use Stsbl\SftpBundle\DependencyInjection\StsblSftpExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class StsblSftpBundle extends Bundle implements AutoloadRoutingBundleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new StsblSftpExtension();
    }
}
