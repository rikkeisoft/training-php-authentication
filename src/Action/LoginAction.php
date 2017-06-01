<?php

namespace App\Action;

use App\Hasher;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

class LoginAction
{
    const MAX_TRY_TIME = 5;

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

        $request = $this->getInputs();

        // TODO: check login by cookie

        // check has submit
        if (empty($request)) {
            $this->showFlashMessage('Not submit data');

            return $this->redirectTo('/login');
        }

        // check too many tries
        if ($this->hasTooManyTries()) {
            $this->showFlashMessage('Too muany tries');

            return $this->redirectTo('/login');
        }

        // check username empty, password empty
        if (!isset($request['username']) || !isset($request['password'])) {
            $this->showFlashMessage('Username and password is required');

            return $this->redirectTo('/login');
        }

        // check username and password format
        if (!$this->checkUsernameFormat($request['username']) || !$this->checkPasswordFormat($request['password'])) {
            $this->showFlashMessage('Username or password is invalid format');
            $this->increasedTryTime();

            return $this->redirectTo('/login');
        }

        // get user by username
        $user = $this->getUserByUsername($request['username']);
        if (empty($user) || !Hasher::match($request['password'], $user['password'])) {
            $this->showFlashMessage('User or Password incorrect');
            $this->increasedTryTime();

            return $this->redirectTo('/login');
        }

        $this->clearTryTime();

        // check user state
        if (!$this->checkUserState($user['state'])) {
            $this->showFlashMessage('User is banned');

            return $this->redirectTo('/login');
        }

        $token = $user['username'] . '|' . md5($user['password']) . '|' . date('YmdHis');
        unset($user['password']);
        $_SESSION['USER'] = $user;

        // Save cookie for remember me
        if ($request['remember-me']) {
            $this->saveCookie('remember_me', $token, 60 * 60 * 24 * 30);
        }

        // TODO:  fire event login success

        $this->showFlashMessage('Login success', 'SUCCESS');

        return $this->redirectTo('/dashboard');
    }

    /**
     * @param int $state
     * @return bool
     */
    protected function checkUserState($state)
    {
        return in_array($state, [1]);
    }

    /**
     * @param string $message
     * @param string $type Optional: 'ERROR', 'SUCCESS'
     * @return void
     */
    protected function showFlashMessage($message, $type = 'ERROR')
    {
        $_SESSION['flash_message'] = compact('message', 'type');
    }

    /**
     * @param string $username
     * @return bool
     */
    protected function checkUsernameFormat($username)
    {
        return preg_match('/\w{3,20}/') > 0;
    }

    /**
     * @param string $password
     * @return bool
     */
    protected function checkPasswordFormat($password)
    {
        return preg_match('/[a-z0-9]{3,20}/') > 0;
    }

    /**
     * @return bool
     */
    protected function hasTooManyTries()
    {
        if (isset($_SESSION['try_time']) && $_SESSION['try_time'] > static::MAX_TRY_TIME) {
            $this->saveCookie('try_time', '1', 15 * 60);
            unset($_SESSION['try_time']);
        }

        return isset($_COOKIE['try_time']);
    }

    /**
     * @return void
     */
    protected function increasedTryTime()
    {
        $_SESSION['try_time'] = isset($_SESSION['try_time']) ? ($_SESSION['try_time'] + 1) : 1;
    }

    /**
     * @return void
     */
    protected function clearTryTime()
    {
        unset($_SESSION['try_time'], $_COOKIE['try_time']);
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $timeout
     */
    protected function saveCookie($key, $value, $timeout)
    {
        setcookie($key, $value, $timeout);
    }

    /**
     * @return array
     */
    protected function getInputs()
    {
        return (array)$this->request->getParsedBody();
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
     * @param int $statusCode
     * @return Response
     */
    protected function redirectTo($url, $statusCode = 301)
    {
        return $this->response->withStatus($statusCode)
            ->withHeader('Location', $url);
    }
}
