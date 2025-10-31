<?php

namespace App\Controllers\Api;

use App\Application\Rules\RecordExist;
use App\Models\User;
use App\Services\Hash;
use App\Services\Resource;
use App\Services\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response, $args) {
        $data = User::all()->map(function(User $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            unset($userData['created_at']);
            unset($userData['updated_at']);
            return $userData;
        });
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    public function create(RequestInterface $request, ResponseInterface $response, $args) {
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            $data = $this->validateCreateUserInput(array_merge(
                    [
                        'name' => null,
                        'email' => null,
                        'password' => null,
                    ], $data
            ));
        } catch(\Exception $e) {
            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Invalid data '. $e->getMessage(),
                ])
            );

            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', 'application/json');
        }

        if (isset($data['password'])) {
            $data['password'] = (new Hash)->make($data['password']);
//            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $resource = new Resource(User::create($data));
        $response->getBody()->write(json_encode($resource));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
    public function update(RequestInterface $request, ResponseInterface $response, $args) {
        $userId = isset($args['userId']) ? (int)$args['userId']: null;
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            $data = $this->validateUpdateUserInput(array_merge(
                    [
                        'id' => $userId,
                        'name' => null,
                        'email' => null,
                        'password' => null,
                    ], $data
            ));
        } catch(\Exception $e) {
            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Invalid data '. $e->getMessage(),
                ])
            );

            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', 'application/json');
        }

        if (isset($data['password'])) {
            $data['password'] = (new Hash)->make($data['password']);
        }

        $user = User::find($userId);

        if(!$user->update($data)) {
            $response->getBody()->write(json_encode(
                [
                    'success' => false,
                    'message' => 'Failed to update user',
                ]
            ));
            return   $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }

        $resource = new Resource(User::create($data));
        $response->getBody()->write(json_encode($resource));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = isset($args['userId']) ? (int)$args['userId'] : null;
        $data = json_decode($request->getBody()->getContents(), true);
        try {
            $data = $this->validateDeleteUserInput(array_merge(
                [
                    'id' => $userId,
                ], $data
            ));
        } catch(\Exception $e) {
            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Invalid data '. $e->getMessage(),
                ])
            );

            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', 'application/json');
        }

        if (!User::find($userId)->delete()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to delete user',
            ]));

            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User deleted successfully',
        ]));

        return $response
            ->withStatus(204)
            ->withHeader('Content-Type', 'application/json');
    }

    private function validateDeleteUserInput(array $data) {
        Validator::validate($data, [
            'id' => [
                new Required(),
                new Type('integer', 'Id must be an integer'),
                new RecordExist([
                    'model' => User::class,
                    'field' => 'id',
                ], 'User doesn\'t exist')
            ],
        ]);

        return ['id' => $data['id']];
    }

    private function validateCreateUserInput(array $data) {
        Validator::validate($data, [
            'name' => [
                new Required(),
                new NotBlank(null, 'Name is required'),
                new Type('string', 'Name must be a string'),
            ],
            'email' => [
                new Required(),
                new NotBlank(null, 'Email is required'),
                new Type('string', 'Email must be a string'),
                new Email(null, 'Email must be a valid email'),
            ],
            'password' => [
                new NotBlank(null, 'Password is required'),
                new Type('string', 'Password must be a string'),
                new Length([
                    'min' => 4,
                    'max' => 150,
                    'minMessage' => 'Password must be at least {{ limit }} characters long',
                    'maxMessage' => 'Password cannot be longer than {{ limit }} characters'
                ]),
            ]
        ]);

        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
    private function validateUpdateUserInput(array $data) {
        Validator::validate($data, [
            'id' => [
                new Required(),
                new Type('integer', 'Id must be an integer'),
                new RecordExist([
                    'model' => User::class,
                    'field' => 'id',
                ], 'User doesn\'t exist')
            ],
            'name' => [
                new Required(),
                new NotBlank(null, 'Name is required'),
                new Type('string', 'Name must be a string'),
            ],
            'email' => [
                new Required(),
                new NotBlank(null, 'Email is required'),
                new Type('string', 'Email must be a string'),
                new Email(null, 'Email must be a valid email'),
            ],
            'password' => [
                new NotBlank(null, 'Password is required'),
                new Type('string', 'Password must be a string'),
                new Length([
                    'min' => 4,
                    'max' => 150,
                    'minMessage' => 'Password must be at least {{ limit }} characters long',
                    'maxMessage' => 'Password cannot be longer than {{ limit }} characters'
                ]),
            ]
        ]);

        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}