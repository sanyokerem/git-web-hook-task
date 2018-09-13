<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class WebHookController extends Controller
{
    /**
     * @Route("/hook/split-repo/{token}", name="test_web_hook", methods={"POST"})
     */
    public function testWebHook(Request $request, $token)
    {
        if ($token === $this->getParameter('app.git_web_hook_token')) {

            if ($request->headers->get('X-GitHub-Event') === 'ping') return new Response('');

            $requestContent = json_decode($request->getContent());

            if (!isset($requestContent->ref)) return new Response('', 404);

            $ref = explode('/', $requestContent->ref);

            $fs = new Filesystem();

            $fs->dumpFile('/var/git-web-hook', end($ref));

            return new Response('', 204);
        }

        return new Response('', 401);
    }
}