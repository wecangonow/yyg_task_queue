<?php

namespace Yyg\Tasks;

class NoticeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        self::send($task);

    }

    public static function send(array $task)
    {
        global $config;
        $key = $config['services']['android_push']['key'];
        $gcm_url = $config['services']['android_push']['gcm_url'];

        $token = "ftpzSwXjvMo:APA91bE6odIenSUZshkLpBmZpXmi-hyV_4g7ZMop1eCTRLfw_7kJNrCN9lTd_mNYJEBhI_RbANXoilsvpklePkin6ZDjxSO18IAkZoTwujM-aP4orcnTJOwZIpIU_H7y7mF2OmDwXJP1";
        $token = "fnoIgCJeBrA:APA91bFgVW0wdMyxKXNbaJMUB11BSmN964jdXaJqPaxbpfR8j8QhZklUl4eEwA-zjgKuiijXLCagj0t07z0Dwze2bDAjSqagmlNJZlnFMLhBICM1aiZHyWsW2W5wQ8mtDt5dh5PfQ_H_";
        $message = ['title' => "This is a test", 'message' => "This is a test", 'tickerText' => "tickerText"];

        $post_data = [
            'registration_ids' => [$token],
            'data' => $message
        ];
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Authorization:' => "key=" . $key
            ]
        ]);

        $response = $client->post($gcm_url, ['json' => $post_data, 'verify' => false]);

        var_dump($response);



    }

}