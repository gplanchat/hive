<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\OAuth;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CallbackController
{
    #[Route('/oauth/callback', name: 'oauth_check')]
    public function __invoke(): Response
    {
        return new Response('Hello world');
    }
}
