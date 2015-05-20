<?php

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Definitions
define('ROOT_DIR', __DIR__);

$app = new Silex\Application();

/*** Services ***/
$app->register(
    new Igorw\Silex\ConfigServiceProvider(
        __DIR__.'/app/config.php'
    )
);

$app['slack.commander'] = $app->share( function () use ($app) {
    $interactor = new \Frlnc\Slack\Http\CurlInteractor;
    $interactor->setResponseFactory(
        new \Frlnc\Slack\Http\SlackResponseFactory
    );

    return new \Frlnc\Slack\Core\Commander(
        $app['slack']['token'],
        $interactor
    );
});

/*** Middlewares ***/
$app->before( function (Request $request, Application $app) {
    // Check Token
    if($app['token'] == 'CHANGEME') {
        return new Response(
            'For security reasons, please change your token inside the config file.'
        );
    }

    // Check Slack
    $slackApiAuthTest = $app['slack.commander']->execute('auth.test')->getBody();

    if(! $slackApiAuthTest['ok']) {
        return new Response(
            'Something seems to be wrong with the slack API: '.$slackApiTest['error']
        );
    }
});

/*** Routes ***/
$app->get('/', function () {
    return 'Hello!';
});

$app->get('/execute', function () use ($app) {
    if($app['token'] != $app['request']->query->get('token')) {
        return 'Invalid token!';
    }

    $executeCommand = new \SlackEmailNotifier\Command\ExecuteCommand($app);

    $results = $executeCommand->execute();

    return $app->json(
        $results
    );
});

$app->run();
