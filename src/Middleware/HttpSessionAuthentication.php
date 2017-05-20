<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpSessionAuthentication
{
    /**
     * Session key for storing authenticated user
     */
    const SESSION_AUTH_KEY = 'USER';

    /**
     * @var string
     */
    protected $loginUrl;

    /**
     * @param string $loginUrl
     * @return self
     */
    public function setLoginUrl($loginUrl)
    {
        $this->loginUrl = (string)$loginUrl;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!$this->loggedIn($request)) {
            return $response->withStatus(301)
                ->withHeader('Location', $this->loginUrl);
        }

        return $next($request, $response);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function loggedIn(ServerRequestInterface $request)
    {
        return isset($_SESSION[static::SESSION_AUTH_KEY]);
    }
}
