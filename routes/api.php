<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
});

Route::get('/users/{user}/children', function (App\Models\User $user) {
    return $user->children;
});
