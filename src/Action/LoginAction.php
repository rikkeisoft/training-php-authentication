<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

class LoginAction
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Constructor
     *
     * @param \Slim\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // TODO: write login action code
    }

    /**
     * @return array
     */
    protected function getInputs()
    {
        return (array) $this->request->getParsedBody();
    }

    /**
     * @param string $username
     * @return array|bool
     */
    protected function getUserByUsername($username)
    {
        $users = $this->app->get('users')['form'];

        foreach ($users as $user) {
            if ($username === $user['username']) {
                return $user;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @param int    $statusCode
     * @return Response
     */
    protected function redirectTo($url, $statusCode = 301)
    {
        return $this->response->withStatus($statusCode)
            ->withHeader('Location', $url);
    }
}
