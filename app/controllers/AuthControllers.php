<?php
declare(strict_types=1);

final class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function csrf(): void
    {
        $this->respond(['token' => $_SESSION['csrf_token']]);
    }

    
    public function me(): void
    {
        $this->respond($this->auth->me());
    }

    public function register(): void
    {
        $this->respond(
            $this->auth->register(readJsonBody()),
            201
        );
    }

    public function login(): void
    {
        $this->respond($this->auth->login(readJsonBody()));
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->respond(null);
    }

    public function profile(): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->respond([
            'id' => (int) $user['id'],
            'name' => $user['ho_ten'],
            'email' => $user['email'],
            'phone' => $user['so_dien_thoai'] ?? '',
            'role' => $user['vai_tro'],
        ]);
    }

    public function updateProfile(): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->respond(
            $this->auth->updateProfile(readJsonBody())
        );
    }

    public function changePassword(): void
    {
        $user = AuthMiddleware::requireAuth();
        $this->auth->changePassword(readJsonBody());
        $this->respond(null);
    }

    private function respond($data, int $status = 200): void
    {
        jsonResponse([
            'ok' => true,
            'data' => $data,
        ], $status);
    }
}
