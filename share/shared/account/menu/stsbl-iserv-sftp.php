<?php

declare(strict_types=1);

use IServ\Bundle\Account\Config\MenuConfigurator;
use IServ\Bundle\Account\Config\MenuIcon;

return static function (MenuConfigurator $config) {
    $config
        ->get('settings')
        ->add('ssh-keys', _('SSH keys'), '/iserv/profile/sftp/keys', new MenuIcon('fa-key-skeleton-left-right'));
};
