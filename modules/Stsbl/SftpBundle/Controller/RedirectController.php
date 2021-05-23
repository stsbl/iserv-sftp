<?php

declare(strict_types=1);

namespace Stsbl\SftpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/keys", name="user_keys")
 */
final class RedirectController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->redirectToRoute('user_sftp_keys', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
