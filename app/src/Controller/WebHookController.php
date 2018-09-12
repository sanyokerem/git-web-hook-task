<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;

class WebHookController extends Controller
{
    /**
     * @Route("/test-webhook/{token}", name="test_web_hook", defaults={"token":""}, methods={"POST"})
     */
    public function testWebHook($token)
    {
        if ($token == '9WPTDSmLAWcmwcTPnFgKSMjl3qhigqLMKo9KB1ne') {

            $fs = new Filesystem();
            $fs->touch('/var/git-web-hook');
        }

        return $this->json([]);
    }
}