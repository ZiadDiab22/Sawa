<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('drivers.{driverId}', function ($user, $driverId) {
    return (int) $user->id === (int) $driverId;
});
