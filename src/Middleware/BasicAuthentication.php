<?php
/**
 * This file is part of `oanhnn/slim-skeleton` project.
 *
 * (c) Oanh Nguyen <oanhnn.bk@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Basic authentication middleware
 */
class BasicAuthentication extends HttpHeaderAuthentication
{
    /**
     * List users [username => password]
     *
     * @var array
     */
    protected $users = [];

    /**
     * Realm of authentication
     *
     * @var string
     */
    protected $realm = 'Login';

    /**
     * Contructor
     *
     * @param array $users List users [username => password]
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * Set the realm value.
     *
     * @param string $realm
     * @return self
     */
    public function realm($realm)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Validate the user and password.
     *
     * @param array                  $credentials  Return value of self::parseAuthorizationHeader()
     * @param ServerRequestInterface $request
     * @return bool|array
     */
    protected function login(array $credentials, ServerRequestInterface $request)
    {
        if (isset($credentials['username']) && isset($credentials['password'])) {
            $username = $credentials['username'];
            $password = $credentials['password'];

            if (isset($this->users[$username]) && ($this->users[$username] === $password)) {
                return compact('username');
            }
        }

        return false;
    }

    /**
     * Parses the authorization header for a basic authentication.
     *
     * @param string $header
     * @return bool|array
     */
    protected function parseAuthorizationHeader($header)
    {
        if (strpos($header, 'Basic') !== 0) {
            return false;
        }
        $header = explode(':', base64_decode(substr($header, 6)), 2);

        return [
            'username' => $header[0],
            'password' => isset($header[1]) ? $header[1] : null,
        ];
    }

    /**
     * Make WWW-Authenticate header
     *
     * @return string
     */
    protected function makeAuthenticateHeader()
    {
        return sprintf('Basic realm="%s"', $this->realm);
    }
}
