<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/test-mail', function () {
//     Mail::raw('Test kirim email dari Laravel ke Mailtrap berhasil ✅', function ($message) {
//         $message->to('test@example.com')->subject('Test Mailtrap');
//     });

//     return 'Email sent! Cek inbox di Mailtrap.';
// });

// routes/web.php
Route::get('/debug-mailer', function () {
    return [
        'mailer'   => config('mail.default'),
        'host'     => config('mail.mailers.smtp.host'),
        'port'     => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'from'     => config('mail.from'),
    ];
});

// routes/web.php
Route::get('/test-mail', function () {
    \Illuminate\Support\Facades\Mail::raw(
        'Test kirim email ke Mailtrap ✅',
        function ($m) { $m->to('dummy@example.com')->subject('Test Mailtrap'); }
    );
    return 'Email sent!';
});
