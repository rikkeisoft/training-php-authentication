<?php

namespace App\Action;

use App\Hasher;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

class LoginAction
{
    const MAX_LOGIN_ATTEMPTS = 5;

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
        if ($this->hasTooManyLoginAttempts()) {
            $this->showFlashMessage('Too many login attempts. Please try again after a few minutes');

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
            $this->incrementLoginAttempts();

            return $this->redirectTo('/login');
        }

        // get user by username
        $user = $this->getUserByUsername($request['username']);
        if (empty($user) || !Hasher::match($request['password'], $user['password'])) {
            $this->showFlashMessage('User or Password incorrect');
            $this->incrementLoginAttempts();

            return $this->redirectTo('/login');
        }

        $this->clearLoginAttempts();

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
        return preg_match('/\w{3,20}/', $username) > 0;
    }

    /**
     * @param string $password
     * @return bool
     */
    protected function checkPasswordFormat($password)
    {
        return preg_match('/[a-z0-9]{3,20}/', $password) > 0;
    }

    /**
     * @return bool
     */
    protected function hasTooManyLoginAttempts()
    {
        if (isset($_COOKIE['login_attempts'])) {
            return true;
        }

        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > static::MAX_LOGIN_ATTEMPTS) {
            $this->saveCookie('login_attempts', 1, 15 * 60);
            $_SESSION['login_attempts'] = 0;

            return true
        }

        return false;
    }

    /**
     * @return void
     */
    protected function incrementLoginAttempts()
    {
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? ($_SESSION['login_attempts'] + 1) : 1;
    }

    /**
     * @return void
     */
    protected function clearLoginAttempts()
    {
        unset($_SESSION['login_attempts'], $_COOKIE['login_attempts']);
        $this->saveCookie('login_attempts', null, -1);
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
