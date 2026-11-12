<?php
declare(strict_types=1);

final class UserModel
{
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, ho_ten, email, mat_khau, so_dien_thoai, vai_tro
             FROM nguoidung
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, ho_ten, email, mat_khau, so_dien_thoai, vai_tro
             FROM nguoidung
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function create(
        string $name,
        string $email,
        string $hash
    ): int {
        $pdo = Database::connection();

        $statement = $pdo->prepare(
            "INSERT INTO nguoidung (ho_ten, email, mat_khau, vai_tro)
             VALUES (:name, :email, :password, 'CUSTOMER')"
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function updateProfile(
        int $id,
        string $name,
        string $phone
    ): void {
        $statement = Database::connection()->prepare(
            'UPDATE nguoidung
             SET ho_ten = :name, so_dien_thoai = :phone
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $name,
            'phone' => $phone === '' ? null : $phone,
        ]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE nguoidung
             SET mat_khau = :password
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'password' => $hash,
        ]);
    }
}