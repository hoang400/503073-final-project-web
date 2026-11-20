<?php
declare(strict_types=1);

final class AuthService
{
    public function __construct(private UserModel $users)
    {
    }

    public function register(array $data): array
    {
        $name = trim($this->text($data, 'name'));
        $email = strtolower(trim($this->text($data, 'email')));
        $password = $this->text($data, 'password');
        $confirm = $this->text($data, 'confirm');

        $this->validateName($name);

        if (
            strlen($email) > 254
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new ApiException('Email không hợp lệ.', 422);
        }

        $this->validatePassword($password, $confirm);

        if ($this->users->findByEmail($email) !== null) {
            throw new ApiException('Email đã được đăng ký.', 409);
        }

        try {
            $id = $this->users->create(
                $name,
                $email,
                password_hash($password, PASSWORD_BCRYPT)
            );
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                throw new ApiException('Email đã được đăng ký.', 409);
            }

            throw $exception;
        }

        $user = $this->users->findById($id);

        if ($user === null) {
            throw new RuntimeException('Created user was not found.');
        }

        return $this->publicUser($user);
    }

    public function login(array $data): array
    {
        $email = strtolower(trim($this->text($data, 'email')));
        $password = $this->text($data, 'password');

        $user = $this->users->findByEmail($email);

        if (
            $user === null
            || strlen($password) > 72
            || str_contains($password, "\0")
            || !password_verify($password, $user['mat_khau'])
        ) {
            throw new ApiException(
                'Email hoặc mật khẩu không đúng.',
                401
            );
        }

        $remember = in_array(
            $data['remember'] ?? null,
            [true, 1, '1', 'on'],
            true
        );

        return $this->signIn($user, $remember);
    }

    public function me(): ?array
    {
        $user = $this->currentUser();

        return $user === null ? null : $this->publicUser($user);
    }

    public function profile(): array
    {
        return $this->publicUser($this->requireUser());
    }

    public function updateProfile(array $data): array
    {
        $user = $this->requireUser();

        $name = trim($this->text($data, 'name'));
        $phone = trim($this->text($data, 'phone'));

        $this->validateName($name);

        if (
            $phone !== ''
            && !preg_match('/\A[0-9+(). \-]{8,20}\z/', $phone)
        ) {
            throw new ApiException(
                'Số điện thoại không hợp lệ.',
                422
            );
        }

        $this->users->updateProfile(
            (int) $user['id'],
            $name,
            $phone
        );

        return $this->profile();
    }

    public function changePassword(array $data): void
    {
        $user = $this->requireUser();

        $current = $this->text($data, 'currentPassword');
        $password = $this->text($data, 'newPassword');
        $confirm = $this->text($data, 'confirm');

        if (
            strlen($current) > 72
            || str_contains($current, "\0")
            || !password_verify($current, $user['mat_khau'])
        ) {
            throw new ApiException(
                'Mật khẩu hiện tại không đúng.',
                422
            );
        }

        $this->validatePassword($password, $confirm);

        $this->users->updatePassword(
            (int) $user['id'],
            password_hash($password, PASSWORD_BCRYPT)
        );

        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function logout(): void
    {
        $_SESSION = [];

        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);

        session_destroy();
    }

    private function currentUser(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;

        if ($id === null) {
            return null;
        }

        $user = $this->users->findById((int) $id);

        if ($user === null) {
            unset($_SESSION['user_id']);
        }

        return $user;
    }

    private function requireUser(): array
    {
        $user = $this->currentUser();

        if ($user === null) {
            throw new ApiException('Vui lòng đăng nhập.', 401);
        }

        return $user;
    }

    private function signIn(array $user, bool $remember = false): array
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // "Ghi nhớ đăng nhập trong 30 ngày": kéo dài cookie phiên
        // (mặc định cookie phiên hết hạn khi đóng trình duyệt).
        if ($remember) {
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            setcookie(session_name(), session_id(), [
                'expires' => time() + 30 * 86400,
                'path' => '/',
                'secure' => $https,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        return $this->publicUser($user);
    }

    private function publicUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => $user['ho_ten'],
            'email' => $user['email'],
            'phone' => $user['so_dien_thoai'] ?? '',
            'role' => $user['vai_tro'],
        ];
    }

    private function text(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        if (!is_string($value)) {
            throw new ApiException(
                'Thông tin gửi lên không hợp lệ.',
                422
            );
        }

        return $value;
    }

    private function validateName(string $name): void
    {
        if (preg_match('/\A.{2,80}\z/u', $name) !== 1) {
            throw new ApiException(
                'Họ tên phải có từ 2 đến 80 ký tự.',
                422
            );
        }
    }

    private function validatePassword(
        string $password,
        string $confirm
    ): void {
        if (
            preg_match('/\A.{8,}\z/us', $password) !== 1
            || strlen($password) > 72
            || str_contains($password, "\0")
        ) {
            throw new ApiException(
                'Mật khẩu cần ít nhất 8 ký tự, tối đa 72 byte.',
                422
            );
        }

        if ($password !== $confirm) {
            throw new ApiException(
                'Mật khẩu xác nhận không khớp.',
                422
            );
        }
    }
}