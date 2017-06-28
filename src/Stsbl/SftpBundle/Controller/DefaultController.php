<?php

namespace Stsbl\SftpBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\BootstrapCollectionType;
use IServ\CoreBundle\Controller\PageController;
use IServ\CoreBundle\Util\Sudo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Default sftp controller
 */
class DefaultController extends PageController
{
    /**
     * Get existing public keys
     *
     * @return array
     */
    private function getPublicKeys()
    {
        $ret = [];
        $ret['keys'] = [];

        $this->get('iserv.sudo');
        $authorizedKeys = $this->getUser()->getHome().'/.ssh/authorized_keys';

        if (Sudo::file_exists($authorizedKeys)) {
            $keys = explode("\n", Sudo::file_get_contents($authorizedKeys));

            foreach ($keys as $key) {
                // ignore empty lines
                if (empty($key)) {
                    continue;
                }
                $ret['keys'][] = $key;
            }
        }

        return $ret;
    }

    /**
     * Store new public ssh keys
     *
     * @param array $keys
     */
    private function storePublicKeys(array $keys)
    {
        $content = implode("\n", $keys);

        $this->get('iserv.sudo');
        $authorizedKeys = $this->getUser()->getHome().'/.ssh/authorized_keys';

        if (!Sudo::is_dir(dirname($authorizedKeys))) {
            Sudo::mkdir(dirname($authorizedKeys));
        }

        Sudo::chmod(dirname($authorizedKeys), 0755);
        Sudo::file_put_contents($authorizedKeys, $content);
        Sudo::chmod($authorizedKeys, 0644);
    }

    /**
     * Get form for public key storing
     *
     * @return Form
     */
    private function getPublicKeyForm()
    {
        $builder = $this->createFormBuilder($this->getPublicKeys());
        $keyRegex = '|^(ssh-rsa)|';
        $builder
            ->add('keys', BootstrapCollectionType::class, [
                'required' => false,
                'label' => _('Public keys'),
                'entry_type' => TextType::class,
                'prototype_name' => 'proto-entry',
                'attr' => [
                    'help_text' => _('Enter the public keys which start with ssh-rsa (example: ssh-rsa AAAAB3Nza[...]== user@example.com)')
                ],
                // Child options
                'entry_options' => [
                    'constraints' => [
                        new NotBlank(['message' => _('You must enter a key.')]),
                        new Regex([
                            'pattern' => $keyRegex,
                            'htmlPattern' => $keyRegex,
                            'message' => _('You must enter a valid public key. Did you may enter a private key?')])
                    ],
                    'attr' => [
                        'widget_col' => 12, // Single child field w/o label col
                    ],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => _('Save'),
                'buttonClass' => 'btn btn-success',
                'icon' => 'floppy-disk',
            ])
        ;

        return $builder->getForm();
    }

    /**
     * Upload public key action
     *
     * @Route("/profile/keys", name="user_keys")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function uploadPublicKeyAction(Request $request)
    {
        $menu = $this->get('iserv.menu.user_profile');
        $form = $this->getPublicKeyForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->storePublicKeys($data['keys']);

            if (count($data['keys']) === 0) {
                $this->get('iserv.flash')->success(_('All stored keys were deleted successful.'));
            } else {
                $this->get('iserv.flash')->success(_n('New key was stored successful.', 'New keys were stored successful.', count($data['keys'])));
            }

            return new RedirectResponse($this->generateUrl('user_keys'));
        }

        $this->addBreadcrumb(_('Profile'), $this->generateUrl('user_profile'));
        $this->addBreadcrumb(_('Keys'));

        return [
            'form' => $form->createView(),
            'menu' => $menu,
        ];
    }
}
