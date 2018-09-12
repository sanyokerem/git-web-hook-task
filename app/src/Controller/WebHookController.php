<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Flex\Response;

class WebHookController extends Controller
{
    /**
     * @Route("/hook/split-repo/{token}", name="test_web_hook", defaults={"token":""}, methods={"POST"})
     */
    public function testWebHook($token)
    {
        if ($token == $this->getParameter('git_web_hook_token')) {

            $fs = new Filesystem();
            $fs->touch('/var/git-web-hook');
        }

        return new Response('', 204);
    }
}