<?php

$config = array(
    'token' => 'CHANGEME', // Used for the cron task http://.../slack-email-notifier/execute?token=CHANGEME)
    'slack' => array(
        'token' => 'xoxp-xxx-xxx-xxx', // Slack Web API Token - https://api.slack.com/web
    ),
    'emails' => array(
        'support@mydomain.com' => array(
            'host' => 'mail.mydomain.com',
            'port' => 993,
            'flags' => '/imap/ssl',
            'username' => 'support@mydomain.com',
            'password' => 'mySuperSecurePassword',
            'slack' => array(
                'username' => 'Mailer',
                'icon_emoji' => ':rooster:',
                'channel' => '#general',
                'text' => 'New mail!',
            ),
        ),
    ),
);

if(file_exists(dirname(__FILE__).'/config-local.php')) {
    $config = array_merge(
        $config,
        include 'config-local.php'
    );
}

return $config;
