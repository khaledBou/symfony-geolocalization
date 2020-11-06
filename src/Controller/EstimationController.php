<?php

namespace App\Controller;

use App\Service\KelQuartierEstimationApiHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur personnalisé pour les estimations KelQuartier.
 */
class EstimationController extends AbstractController
{
    /**
     * @var KelQuartierEstimationApiHelper
     */
    private $kelQuartierEstimationApiHelper;

    /**
     * @param KelQuartierEstimationApiHelper $kelQuartierEstimationApiHelper
     */
    public function __construct(KelQuartierEstimationApiHelper $kelQuartierEstimationApiHelper)
    {
        $this->kelQuartierEstimationApiHelper = $kelQuartierEstimationApiHelper;
    }

    /**
     * Récupération d'une estimation auprès de l'API KelQuartier.
     *
     * @see http://pro.kelquartier.com/api_estimation_documentation.html
     *
     * @Route(
     *     name="get_collection_estimation",
     *     path="/estimation",
     *     methods={"GET"},
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function estimation(Request $request): JsonResponse
    {
        $result = $this->kelQuartierEstimationApiHelper->call('getEstimation.php', $request->query->all());

        return new JsonResponse($result);
    }
}
