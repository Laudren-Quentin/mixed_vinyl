<?php

namespace App\Service;


use Psr\Cache\CacheItemInterface;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MixRepository
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(
        private HttpClientInterface $githubContentClient,
        CacheInterface $cache,
        #[Autowire('%kernel.debug%')]
        private bool $isDebug,
        #[Autowire(service:'twig.command.debug')]
        private DebugCommand $twigDebugCommand,
    )
    {
        $this->cache = $cache;
    }
    public function findAll(): array
    {
        dd($this->githubContentClient);

        $output = new BufferedOutput();
        $this->twigDebugCommand->run(new ArrayInput([]), $output);
        return $this->cache->get('mixes_data', function(CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter($this->isDebug ? 5 : 60);
            $response = $this->githubContentClient->request('GET', '/SymfonyCasts/vinyl-mixes/main/mixes.json');
            return $response->toArray();
        });
    }
}
