<?php

namespace App\Lib;

use App\Models\TaskModel;
use App\Util\Env;
use App\Models\NotifyModel;
use DateTime;
use DateTimeZone;

Env::ParseEnv();

// TODO
define("TMP_TIMEZONE", "Europe/Sofia");

class Notify
{
    private static function createJWT(string $tokenUri, string $clientEmail, string $privateKey): string
    {
        $header = base64_encode(
            json_encode(
                [
                    'alg' => 'RS256',
                    'typ' => 'JWT',
                ]
            )
        );

        $now = time();

        $payload = base64_encode(
            json_encode(
                [
                    'iss' => $clientEmail,
                    'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                    'aud' => $tokenUri,
                    'exp' => $now + 600, // 10 mins
                    'iat' => $now,
                ]
            )
        );

        $signature = '';

        openssl_sign(
            $header . '.' . $payload,
            $signature,
            $privateKey,
            'sha256'
        );

        $signature = base64_encode($signature);

        $jwt = $header . '.' . $payload . '.' . $signature;

        return $jwt;
    }

    private static function getAccessToken(): string
    {
        $clientEmail = getenv("client_email");
        $privateKey = json_decode(getenv("private_key"));
        $tokenUri = 'https://oauth2.googleapis.com/token';

        $jwt = Notify::createJWT($tokenUri, $clientEmail, $privateKey);


        $postData = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $tokenUri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            exit('Failed to fetch access token');
        }

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            return $data['access_token'];
        } else {
            exit('Error fetching access token: '.$data['error_description']);
            return null;
        }
    }


    public static function createCronJob(int $start_timestamp, string $title): int|null
    {
        $user_id = json_decode(base64_decode($_COOKIE["user_data"]), true)["id"];
        $device_token = NotifyModel::get_fcm_device_token_for($user_id);
        if ($device_token === false) {
            return null;
        }

        $access_token = Notify::getAccessToken();

        $fcm_url = 'https://fcm.googleapis.com/v1/projects/yata-43e21/messages:send';

        $fcm_headers = [
            "Authorization" => "Bearer $access_token",
            "content-type" => "application/json"
        ];

        $fcm_data = [
            "message" => [
                "token" => $device_token,
                "data" => [
                    "title" => "Task: $title",
                    "text" => "Reminder that you should be working on your task: $title",
                ],
            ]
        ];


        $cron_job_api_key = getenv("CRON_JOB_API_KEY");

        $headers = [
            "Authorization: Bearer $cron_job_api_key",
            'content-type: application/json'
        ];

        $date = new DateTime();
        $date->setTimestamp($start_timestamp);
        $date->setTimezone(new DateTimeZone(TMP_TIMEZONE));
        $minute = (int)$date->format('i');
        $hour = (int)$date->format('H');
        $day = (int)$date->format('d');
        $month = (int)$date->format('m');

        $expires_at = $date->modify("+ 5 minute")->format('YmdHis');

        $user_name = json_decode(base64_decode($_COOKIE["user_data"]), true)["name"];

        $data = json_encode([
            "job" => [
                "url" => $fcm_url,
                "enabled" => true,
                "title" => "$user_name - $title",
                "saveResponses" => true,
                "requestMethod" => 1,
                "schedule" => [
                    "timezone" => TMP_TIMEZONE,
                    "expiresAt" => $expires_at,
                    "minutes" => [$minute],
                    "hours" => [$hour],
                    "mdays" => [$day],
                    "months" => [$month],
                    "wdays" => [-1],
                ],
                "extendedData" => [
                    "headers" => $fcm_headers,
                    "body" => json_encode($fcm_data),
                ],
            ],
        ]);

        $ch = curl_init("https://api.cron-job.org/jobs");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return (int)json_decode($response, true)["jobId"];
    }


    public static function deleteCronJob(int $task_id): void
    {
        $cron_job_id = TaskModel::getCronJobId($task_id);
        if ($cron_job_id === null) {
            return;
        }

        $cron_job_api_key = getenv("CRON_JOB_API_KEY");

        $headers = [
            "Authorization: Bearer $cron_job_api_key",
            'content-type: application/json'
        ];

        $ch = curl_init("https://api.cron-job.org/jobs/$cron_job_id");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
    }


    public static function updateCronJob(int $task_id, int $start_timestamp, string $title): void
    {
        $cron_job_id = TaskModel::getCronJobId($task_id);
        if ($cron_job_id === null) {
            return;
        }

        $user_id = json_decode(base64_decode($_COOKIE["user_data"]), true)["id"];
        $device_token = NotifyModel::get_fcm_device_token_for($user_id);
        if ($device_token === false) {
            return;
        }

        $access_token = Notify::getAccessToken();

        $fcm_url = 'https://fcm.googleapis.com/v1/projects/yata-43e21/messages:send';

        $fcm_headers = [
            "Authorization" => "Bearer $access_token",
            "content-type" => "application/json"
        ];

        $fcm_data = [
            "message" => [
                "token" => $device_token,
                "data" => [
                    "title" => "Task: $title",
                    "text" => "Reminder that you should be working on your task: $title",
                ],
            ]
        ];


        $cron_job_api_key = getenv("CRON_JOB_API_KEY");

        $headers = [
            "Authorization: Bearer $cron_job_api_key",
            'content-type: application/json'
        ];

        $date = new DateTime();
        $date->setTimestamp($start_timestamp);
        $date->setTimezone(new DateTimeZone(TMP_TIMEZONE));
        $minute = (int)$date->format('i');
        $hour = (int)$date->format('H');
        $day = (int)$date->format('d');
        $month = (int)$date->format('m');

        $expires_at = $date->modify("+ 5 minute")->format('YmdHis');

        $user_name = json_decode(base64_decode($_COOKIE["user_data"]), true)["name"];

        $data = json_encode([
            "job" => [
                "url" => $fcm_url,
                "enabled" => true,
                "title" => "$user_name - $title",
                "saveResponses" => true,
                "requestMethod" => 1,
                "schedule" => [
                    "timezone" => TMP_TIMEZONE,
                    "expiresAt" => $expires_at,
                    "minutes" => [$minute],
                    "hours" => [$hour],
                    "mdays" => [$day],
                    "months" => [$month],
                    "wdays" => [-1],
                ],
                "extendedData" => [
                    "headers" => $fcm_headers,
                    "body" => json_encode($fcm_data),
                ],
            ],
        ]);

        $ch = curl_init("https://api.cron-job.org/jobs/$cron_job_id");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
    }


    public static function send_notification(string $title, string $text): void
    {
        $id = json_decode(base64_decode($_COOKIE["user_data"]), true)["id"];
        $device_token = NotifyModel::get_fcm_device_token_for($id);
        if ($device_token === false) {
            return;
        }

        $access_token = Notify::getAccessToken();

        $url = 'https://fcm.googleapis.com/v1/projects/yata-43e21/messages:send';
        $headers = [
            "Authorization: Bearer $access_token",
            'content-type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $device_token,
                "data" => [
                    "title" => $title,
                    "text" => $text,
                ],
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
    }
}
