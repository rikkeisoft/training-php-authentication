<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract class HttpHeaderAuthentication
 */
abstract class HttpHeaderAuthentication
{
    /**
     * Attribute key for storing username
     */
    const ATTR_KEY = 'USER';

    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $credentials = $this->parseAuthorizationHeader($request->getHeaderLine('Authorization'));
        if ($credentials && ($user = $this->login($credentials, $request))) {
            return $next(
                $request->withAttribute(static::ATTR_KEY, $user),
                $response
            );
        }

        return $response
            ->withStatus(401)
            ->withHeader('WWW-Authenticate', $this->makeAuthenticateHeader());
    }

    /**
     * Validate the user and password.
     *
     * @param array                  $credentials  Return value of self::parseAuthorizationHeader()
     * @param ServerRequestInterface $request
     * @return bool|array   Return user information or FALSE
     */
    abstract protected function login(array $credentials, ServerRequestInterface $request);

    /**
     * Parses the authorization header for a basic authentication.
     *
     * @param string $header
     * @return bool|array
     */
    abstract protected function parseAuthorizationHeader($header);

    /**
     * Make WWW-Authenticate header
     *
     * @return string
     */
    abstract protected function makeAuthenticateHeader();
}