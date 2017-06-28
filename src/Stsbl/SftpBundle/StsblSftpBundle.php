<?php

namespace Stsbl\SftpBundle;

use IServ\CoreBundle\Routing\AutoloadRoutingBundleInterface;
use Stsbl\SftpBundle\DependencyInjection\StsblSftpExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StsblSftpBundle extends Bundle implements AutoloadRoutingBundleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new StsblSftpExtension();
    }
}
