<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Scheduled;

use FediE2EE\PKDServer\ServerConfig;
use GuzzleHttp\Client;
use ParagonIE\EasyDB\EasyDB;

/**
 *
 */
class Witness
{
    private EasyDB $db;
    private Client $http;
    public function __construct(private readonly ServerConfig $config)
    {
        $this->db = $config->getDB();
        $this->http = $this->config->getGuzzle();
    }

    public function run(): void
    {}
}
