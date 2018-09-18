<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class WebHookController extends Controller
{
    public function splitRepo(Request $request, $token)
    {
        if (!$token === $this->getParameter('app.git_web_hook_token')) {
            return new Response('', 401);
        }

        if ($request->headers->get('X-GitHub-Event') === 'ping') {
            return new Response('');
        }

        $requestContent = json_decode($request->getContent());

        if (!isset($requestContent->ref)) {
            return new Response('', 404);
        }

        $ref = explode('/', $requestContent->ref);
        $branch = end($ref);

        if (preg_match('#^(\d+(\.\d+)*|master)$#uis', $branch)) {
            $fs = new Filesystem();
            $fs->dumpFile($this->getParameter('app.hook_dir') . '/git-web-hook', $branch);
        }

        return new Response('', 204);
    }
}