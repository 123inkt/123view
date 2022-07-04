<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (FrameworkConfig $framework): void {
    $httpClient = $framework->httpClient();
    $httpClient->defaultOptions()
        ->verifyHost(env('HTTP_CLIENT_VERIFY_HOST')->bool())
        ->verifyPeer(env('HTTP_CLIENT_VERIFY_PEER')->bool());

    $httpClient->scopedClient('upsource.client')
        ->authBasic('%env(UPSOURCE_BASIC_AUTH)%')
        ->baseUri('%env(UPSOURCE_API_URL)%~rpc/')
        ->scope('upsource');

    $httpClient->scopedClient('gitlab.client')
        ->baseUri('%env(GITLAB_API_URL)%api/v4/')
        ->header('PRIVATE-TOKEN', '%env(GITLAB_ACCESS_TOKEN)%')
        ->scope('gitlab');
};
