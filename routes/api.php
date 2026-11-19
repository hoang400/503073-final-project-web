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


$admin = new AdminController(
    new AdminModel()
);

$staff = new StaffController(
    new StaffModel()
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


    // ADMIN ROUTES

    
    '/admin/movies' => [
        'GET' => [$admin, 'getMovies'],
        'POST' => [$admin, 'createMovie'],
    ],

    '/admin/movies/{id}' => [
        'GET' => [$admin, 'getMovie'],
        'PATCH' => [$admin, 'updateMovie'],
        'DELETE' => [$admin, 'deleteMovie'],
    ],


    '/admin/cinemas' => [
        'GET' => [$admin, 'getCinemas'],
        'POST' => [$admin, 'createCinema'],
    ],

    '/admin/cinemas/{id}' => [
        'PATCH' => [$admin, 'updateCinema'],
        'DELETE' => [$admin, 'deleteCinema'],
    ],

    '/admin/rooms' => [
        'GET' => [$admin, 'getRooms'],
        'POST' => [$admin, 'createRoom'],
    ],

    '/admin/rooms/{id}' => [
        'PATCH' => [$admin, 'updateRoom'],
        'DELETE' => [$admin, 'deleteRoom'],
    ],

    '/admin/rooms/{id}/seats' => [
        'GET' => [$admin, 'getRoomSeats'],
        'POST' => [$admin, 'createSeat'],
    ],

    '/admin/seats/{id}' => [
        'DELETE' => [$admin, 'deleteSeat'],
    ],

    '/admin/showtimes' => [
        'GET' => [$admin, 'getShowtimes'],
        'POST' => [$admin, 'createShowtime'],
    ],

    '/admin/showtimes/{id}' => [
        'PATCH' => [$admin, 'updateShowtime'],
        'DELETE' => [$admin, 'deleteShowtime'],
    ],

    '/admin/concessions' => [
        'GET' => [$admin, 'getConcessions'],
        'POST' => [$admin, 'createConcession'],
    ],

    '/admin/concessions/{id}' => [
        'PATCH' => [$admin, 'updateConcession'],
        'DELETE' => [$admin, 'deleteConcession'],
    ],

    '/admin/bookings' => [
        'GET' => [$admin, 'getBookings'],
    ],

    '/admin/bookings/{id}' => [
        'GET' => [$admin, 'getBookingDetail'],
        'POST' => [$admin, 'confirmBooking'],
    ],

    '/admin/bookings/{id}/cancel' => [
        'POST' => [$admin, 'cancelBooking'],
    ],

    '/admin/reports' => [
        'GET' => [$admin, 'getReport'],
    ],


    // STAFF ROUTES
    
    '/staff/check-in' => [
        'POST' => [$staff, 'verifyTicket'],
    ],

    '/staff/check-in/confirm' => [
        'POST' => [$staff, 'confirmCheckin'],
    ],
];
