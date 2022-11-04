<?php

declare(strict_types=1);

namespace Stsbl\SftpBundle\Controller;

use IServ\CoreBundle\Controller\AbstractPageController;
use Knp\Menu\ItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stsbl\SftpBundle\Form\Type\SshKeysType;
use Stsbl\SftpBundle\Model\AuthorizedKeysFile;
use Stsbl\SftpBundle\Service\SshKeys;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to handle reading and writing of authorized SSH keys for the user.
 */
final class KeyController extends AbstractPageController
{
    public function __construct(
        private readonly ItemInterface $userProfileMenu,
    ) {
    }

    /**
     * Upload public key action
     *
     * @Route("/profile/sftp/keys", name="user_sftp_keys")
     * @Template()
     */
    public function uploadPublicKeys(Request $request, SshKeys $handler): RedirectResponse|array
    {
        $form = $this->createForm(SshKeysType::class, $handler->fetchPublicKeys());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthorizedKeysFile $authorizedKeys */
            $authorizedKeys = $form->getData();

            $handler->putPublicKeys($authorizedKeys);

            if (!$authorizedKeys->hasKeys()) {
                $this->flashMessage()->success(_('All stored keys were deleted successful.'));
            } else {
                $this->flashMessage()->success(_n(
                    'New key was stored successful.',
                    'New keys were stored successful.',
                    $authorizedKeys->countKeys()
                ));
            }

            return $this->redirectToRoute('user_keys');
        }

        $this->addBreadcrumb(_('Profile'), $this->generateUrl('user_profile'));
        $this->addBreadcrumb(_('Keys'));

        return [
            'form' => $form->createView(),
            'menu' => $this->userProfileMenu,
        ];
    }
}
