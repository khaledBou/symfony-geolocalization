<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Manipulation de l'API "estimation" KelQuartier.
 */
class KelQuartierEstimationApiHelper
{
    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->apiUrl = $parameterBag->get('kel_quartier_estimation_api_url');
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
            'format' => 'JSON',
        ]);

        $client = new \GuzzleHttp\Client();
        $url = sprintf('%s/%s?%s', $this->apiUrl, $endpoint, http_build_query($parameters));
        $response = $client->request('GET', $url);

        return json_decode($response->getBody(), true);
    }
}
