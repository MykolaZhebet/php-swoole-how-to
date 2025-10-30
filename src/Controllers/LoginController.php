<?php
namespace App\Controllers;

use App\Application\Rules\RecordExist;
use App\Events\EventLogin;
use App\Infrastructure\Services\SessionTable;
use App\Models\User;
use App\Services\Event;
use App\Services\Validator;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LoginController {

    public function login(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('login'));
        return $response;
    }

    public function loginhandler(RequestInterface $request, ResponseInterface $response, $args) {
        global $app;

        $data = $request->getParsedBody();
        unset($data['submit']);
        try {
            /** @throws \Exception */
            $this->validateLoginForm($data);
        } catch (\Exception $e) {
            return $response
                ->withHeader('Location', 'login?error=Authentication error '. $e->getMessage())
                ->withStatus(302);
        }


        $user = User::where('email', $data['email'])->first();
        //Todo: verify if the user was found
        if (!password_verify($data['password'], $user->password)) {
            $app->getContainer()->get('logger')->info('Wrong password!');
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate')
                ->withStatus(302);
        }

        $sessionTable = SessionTable::getInstance();
        $sessionTable->set($request->session['id'], [
            'id' => $request->session['id'],
            'user_id' => $user->id,
        ]);

        Event::dispatch(new EventLogin($user));
        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args) {
        //Todo validation
        $sessionTable = SessionTable::getInstance();
        $sessionTable->set($request->session['id'], [
            'id' => $request->session['id']
        ]);

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    private function validateLoginForm(array $data): void {
        /** @throws \Exception */
        Validator::validate($data, [
            'email' => [
                new NotBlank(null, 'Email is required'),
                new Type('string', 'Email must be a string'),
                new Email(null, 'Email must be a valid email'),
                new RecordExist([
                    'model' => User::class,
                    'field' => 'email',
                ], 'Email doesn\'t exist')
            ],
            'password' => [
                new NotBlank(null, 'Password is required'),
                new Type('string', 'Password must be a string'),
            ]
        ]);
    }
}
