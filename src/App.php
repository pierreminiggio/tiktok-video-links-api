<?php

namespace App;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class App
{

    public function __construct(
        private string $baseDir,
        private string $host
    )
    {
    }

    public function run(string $path, ?string $queryParameters): void
    {
        if ($path === '/') {
            http_response_code(404);

            return;
        }

        $request = substr($path, 1);

        $videoId = $request;

        $config = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';
        $dbConfig = $config['db'];
        $fetcher = new DatabaseFetcher(new DatabaseConnection(
            $dbConfig['host'],
            $dbConfig['database'],
            $dbConfig['username'],
            $dbConfig['password'],
            DatabaseConnection::UTF8_MB4
        ));

        $queriedVideos = $fetcher->query(
            $fetcher->createQuery(
                'tiktok_video'
            )->select(
                'tiktok_url'
            )->where(
                'id = :id'
            ),
            ['id' => $videoId]
        );

        if (! $queriedVideos) {
            http_response_code(404);
            return;
        }

        $queriedVideo = $queriedVideos[0];
        $tiktokUrl = $queriedVideo['tiktok_url'];
        
        if (! $tiktokUrl) {
            http_response_code(404);
            return;
        }

        header('Location: ' . $tiktokUrl);

        echo <<<HTML
            <a href="$tiktokUrl">Click here if the automatic redirection is not working ...</a>
        HTML;
    }
}
