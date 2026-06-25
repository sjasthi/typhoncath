<?php
namespace App\Modules\Admin;

class UserService
{
    private UserRepository $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function validateUserInput(array $data, bool $isEdit): array
    {
        $errors = [];

        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Name is required.';
        }

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        $password = $data['password'] ?? '';
        if (!$isEdit && strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($isEdit && $password !== '' && strlen($password) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if (empty($data['role_id'])) {
            $errors[] = 'Role is required.';
        }

        return $errors;
    }

    public function createUser(array $data): int
    {
        return $this->repo->insert([
            'name'          => trim($data['name']),
            'email'         => trim($data['email']),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role_id'       => (int)$data['role_id'],
        ]);
    }

    public function updateUser(int $id, array $data): void
    {
        $this->repo->update($id, [
            'name'    => trim($data['name']),
            'email'   => trim($data['email']),
            'role_id' => (int)$data['role_id'],
        ]);

        if (trim($data['password'] ?? '') !== '') {
            $this->repo->updatePassword($id, password_hash($data['password'], PASSWORD_BCRYPT));
        }
    }
}
