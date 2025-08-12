<?php

declare(strict_types=1);

namespace Stsbl\SftpBundle\Controller;

use IServ\Bundle\Account\Controller\AbstractAccountController;
use IServ\Bundle\Account\Menu\AccountBreadcrumbs;
use IServ\Bundle\Flash\Flash\FlashInterface;
use IServ\Library\ModuleResponse\ResponseContent;
use Stsbl\SftpBundle\Form\Type\SshKeysType;
use Stsbl\SftpBundle\Model\AuthorizedKeysFile;
use Stsbl\SftpBundle\Service\SshKeys;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to handle reading and writing of authorized SSH keys for the user.
 */
final class KeyController extends AbstractAccountController
{
    public function __construct(
        private readonly AccountBreadcrumbs $accountBreadcrumbs,
        private readonly FlashInterface $flash,
    ) {
    }

    /**
     * Upload public key action
     *
     */
    #[Route("/profile/sftp/keys", name: "user_sftp_keys")]
    public function uploadPublicKeys(Request $request, SshKeys $handler): RedirectResponse|ResponseContent
    {
        $form = $this->createForm(SshKeysType::class, $handler->fetchPublicKeys());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthorizedKeysFile $authorizedKeys */
            $authorizedKeys = $form->getData();

            $handler->putPublicKeys($authorizedKeys);

            if (!$authorizedKeys->hasKeys()) {
                $this->flash->success(_('All stored keys were deleted successful.'));
            } else {
                $this->flash->success(_n(
                    'New key was stored successful.',
                    'New keys were stored successful.',
                    $authorizedKeys->countKeys()
                ));
            }

            return $this->redirectToRoute('user_keys');
        }

        $content = $this->renderView('@StsblSftp/key/upload_public_keys.html.twig', ['form' => $form->createView()]);

        $response = $this->createResponseBuilder($content)
            ->addBreadcrumb($this->accountBreadcrumbs->root()->getLabel(), $this->accountBreadcrumbs->root()->getUrl())
            ->addBreadcrumb($this->accountBreadcrumbs->settings()->getLabel(), $this->accountBreadcrumbs->settings()->getUrl())
            ->addBreadcrumb(_('Keys'))
            ->setTitle(_('Keys'))
        ;

        return $response->getResponseContent();
    }
}
