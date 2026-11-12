<?php
declare(strict_types=1);

$auth = new AuthController(
    new AuthService(new UserModel())
);

$movies = new MovieController(
    new MovieModel()
);

$orders = new OrderController(
    new OrderModel()
);

$booking = new BookingController(
    new BookingModel()
);

return [
    '/csrf' => [
        'GET' => [$auth, 'csrf'],
    ],

    '/auth/me' => [
        'GET' => [$auth, 'me'],
    ],

    '/auth/register' => [
        'POST' => [$auth, 'register'],
    ],

    '/auth/login' => [
        'POST' => [$auth, 'login'],
    ],

    '/auth/logout' => [
        'POST' => [$auth, 'logout'],
    ],

    '/profile' => [
        'GET' => [$auth, 'profile'],
        'PATCH' => [$auth, 'updateProfile'],
    ],

    '/profile/password' => [
        'POST' => [$auth, 'changePassword'],
    ],
    
    '/movies' => [
        'GET' => [$movies, 'index'],
    ],

    '/showtimes' => [
        'GET' => [$booking, 'showtimes'],
    ],

    '/seats' => [
        'GET' => [$booking, 'seats'],
    ],

    '/concessions' => [
        'GET' => [$booking, 'concessions'],
    ],

    '/holds' => [
        'POST' => [$booking, 'holdSeats'],
    ],

    '/checkout' => [
        'POST' => [$booking, 'checkout'],
    ],
    
    '/orders' => [
        'GET' => [$orders, 'index'],
    ],

    '/order' => [
        'GET' => [$orders, 'detail'],
    ],

];