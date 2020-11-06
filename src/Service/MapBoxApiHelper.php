<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Manipulation de l'API MapBox.
 *
 * @see https://account.mapbox.com/
 * @see https://passbolt.oryx-immobilier.com/app/passwords/view/e243ba95-bdc3-4e45-9ff0-549fc7bdd859
 */
class MapBoxApiHelper
{
    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->apiUrl = $parameterBag->get('mapbox_api_url');
        $this->apiToken = $parameterBag->get('mapbox_api_token');
    }

    /**
     * Fait un appel à l'API.
     *
     * @param string $endpoint   L'endpoint, sans leading slash
     * @param array  $parameters
     *
     * @return array
     */
    public function call(string $endpoint, array $parameters): array
    {
        $parameters = array_merge($parameters, [
            'access_token' => $this->apiToken,
            'cache_buster' => (int) microtime(true) * 1000,
        ]);

        $client = new \GuzzleHttp\Client();
        $url = sprintf('%s/%s?%s', $this->apiUrl, $endpoint, http_build_query($parameters));
        $response = $client->request('GET', $url);

        return json_decode($response->getBody(), true);
    }
}
