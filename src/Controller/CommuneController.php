<?php

namespace App\Controller;

use App\Entity\Commune;
use App\Service\MapBoxApiHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur personnalisé pour les communes.
 */
class CommuneController extends AbstractController
{
    /**
     * @var MapBoxApiHelper
     */
    private $mapBoxApiHelper;

    /**
     * @param MapBoxApiHelper $mapBoxApiHelper
     */
    public function __construct(MapBoxApiHelper $mapBoxApiHelper)
    {
        $this->mapBoxApiHelper = $mapBoxApiHelper;
    }

    /**
     * Recherche de communes par autocomplétion.
     *
     * @Route(
     *     name="get_collection_autocomplete",
     *     path="/communes/autocomplete/{query}",
     *     methods={"GET"},
     * )
     *
     * @param string $query La chaîne recherchée
     *
     * @return JsonResponse
     */
    public function autocomplete(string $query): JsonResponse
    {
        /**
         * Les résultats autocomplétés depuis MapBox.
         *
         * @var array
         *
         * @see https://docs.mapbox.com/api/search/
         * @see https://docs.mapbox.com/search-playground/
         */
        $mapBoxResults = $this->mapBoxApiHelper->call(
            sprintf('geocoding/v5/mapbox.places/%s.json', $query),
            [
                'types' => 'district,postcode,locality,neighborhood,address,place',
                'autocomplete' => 'true',
                'country' => 'fr',
                'language' => 'fr',
                'limit' => 10, // c'est le maximum
            ]
        );

        /**
         * Les coordonnées des résultats issus de MapBox.
         *
         * @var array[]
         */
        $coordinates = [];
        foreach ($mapBoxResults['features'] as $feature) {
            $coordinates[] = $feature['geometry']['coordinates'];
        }

        /**
         * Les villes correspondantes aux résultats issus de MapBox.
         *
         * @var string[]
         */
        $communes = $this
            ->getDoctrine()
            ->getRepository(Commune::class)
            ->findNearestFromCoordinates($coordinates)
        ;

        /**
         * Les résultats à retourner.
         *
         * Il s'agit d'un tableau de tableaux contenant :
         *     - le libellé du résultat autocomplété par MapBox
         *     - l'id de la commune correspondante dans l'API
         *     - le nom de la commune correspondante dans l'API
         *     - l'alias de la commune correspondante dans l'API
         *
         * @return array[]
         */
        $results = [];
        foreach ($communes as $i => $commune) {
            $results[] = [
                'searchResultLabel' => $mapBoxResults['features'][$i]['place_name_fr'],
                'communeId' => $commune['id'],
                'communeNom' => $commune['nom'],
                'communeAlias' => $commune['alias'],
            ];
        }

        return new JsonResponse($results);
    }

    /**
     * Récupère les communes représentant des arrondissements (Lyon 7ème, Paris 16ème, …),
     * et leurs communes parentes (Lyon, Paris, …).
     *
     * @Route(
     *     name="get_collection_arrondissements",
     *     path="/communes/arrondissements",
     *     methods={"GET"},
     * )
     *
     * @return JsonResponse
     */
    public function arrondissements(): JsonResponse
    {
        $results = $this
            ->getDoctrine()
            ->getRepository(Commune::class)
            ->findArrondissements()
        ;

        return new JsonResponse($results);
    }
}
