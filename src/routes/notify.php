<?php

use App\Controllers\NotifyController;
use App\Routing\Router;


Router::postProtected("/api/set-fcm-token", function () {
    NotifyController::update_fcm_device_token();
});

Router::postProtected("/api/update-notification-status", function () {
    NotifyController::update_notification_status();
});

Router::postProtected("/api/get-notification-status", function () {
    NotifyController::get_notification_status();
});
