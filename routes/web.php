<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/register', [
        'uses' => 'AuthController@register'
    ]);

    $router->post('/login', [
        'uses' => 'AuthController@login'
    ]);
});

$router->group(['prefix' => 'books'], function () use ($router) {
    $router->get('/', [
        'uses' => 'BookController@getBooks'
    ]);

    $router->get('/{bookId}', [
        'uses' => 'BookController@getBook'
    ]);
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/{userId}', [
            'uses' => 'UserController@showOneUser'
        ]);

        $router->put('/{userId}', [
            'uses' => 'UserController@updateUser'
        ]);

        $router->delete('/{userId}', [
            'uses' => 'UserController@deleteUser'
        ]);
    });

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->get('/', [
            'uses' => 'TransactionController@showTransaction'
        ]);

        $router->get('/{transactionId}', [
            'uses' => 'TransactionController@getTransactionbyid'
        ]);
    });
});

$router->group(['middleware' => 'auth:admin'], function () use ($router) {
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/', [
            'uses' => 'UserController@getAllUsers'
        ]);
    });

    $router->group(['prefix' => 'books'], function () use ($router) {
        $router->post('/', [
            'uses' => 'BookController@createBook'
        ]);

        $router->put('/{bookId}', [
            'uses' => 'BookController@updateBook'
        ]);

        $router->delete('/{bookId}', [
            'uses' => 'BookController@deleteBook'
        ]);
    });

    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->put('/{transactionId}', [
            'uses' => 'TransactionController@editTransaction'
        ]);
    });
});

$router->group(['middleware' => 'auth:user'], function () use ($router) {
    $router->group(['prefix' => 'transactions'], function () use ($router) {
        $router->post('/', [
            'uses' => 'TransactionController@CreateTransactions'
        ]);
    });
});
