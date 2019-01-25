<?php declare(strict_types = 1);

namespace Stsbl\SftpBundle\Controller;

use IServ\CoreBundle\Controller\AbstractPageController;
use Knp\Menu\ItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stsbl\SftpBundle\Form\Type\SshKeysType;
use Stsbl\SftpBundle\Model\AuthorizedKeyFile;
use Stsbl\SftpBundle\Service\SshKeys;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller to handle reading and writing of authorized SSH keys for the user.
 */
class KeyController extends AbstractPageController
{
    /**
     * @var ItemInterface
     */
    private $userProfileMenu;

    /**
     * Upload public key action
     *
     * @Route("/profile/keys", name="user_keys")
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function uploadPublicKeys(Request $request, SshKeys $handler)
    {
        $form = $this->createForm(SshKeysType::class, $handler->fetchPublicKeys());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthorizedKeyFile $authorizedKeys */
            $authorizedKeys = $form->getData();

            $handler->putPublicKeys($authorizedKeys);

            if (!$authorizedKeys->hasKeys()) {
                $this->addFlash('success', _('All stored keys were deleted successful.'));
            } else {
                $this->addFlash('success', _n(
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

    /**
     * @required
     */
    public function setUserProfileMenu(ItemInterface $userProfileMenu): void
    {
        $this->userProfileMenu = $userProfileMenu;
    }
}
