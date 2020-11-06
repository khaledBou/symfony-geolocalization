<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;

/**
 * Estimation.
 *
 * @see http://pro.kelquartier.com/api_estimation_documentation.html
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get_estimation"={
 *             "method"="GET",
 *             "route_name"="get_collection_estimation",
 *             "controller"=EstimationController::class,
 *             "openapi_context"={
 *                 "summary"="Calls KelQuartier API to get an estimate.",
 *                 "parameters"={
 *                     {
 *                         "in"="query",
 *                         "name"="adr",
 *                         "type"="string",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="lon",
 *                         "type"="string",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="lat",
 *                         "type"="string",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="format",
 *                         "type"="string",
 *                         "required"=false,
 *                     },
 *                     {
 *                          "in"="query",
 *                          "name"="type",
 *                          "type"="int",
 *                          "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="vue",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="surf",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="terrain",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="etg",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="nb_etg",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="annee_constr",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="dpe",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="nb_p",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="nb_c",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="nb_sdb",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="surf_balc_terra",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="asc",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="park",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="cave",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="pisc",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="tennis",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_a_n",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_a_p",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_peint",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_elec",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_plomb",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_sol",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_cuis",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="r_sdb",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="bain_douc",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="balc_terra",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="bbc",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="chauf",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="clim",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="cuis",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="cuis_eq",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="exp",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="imm_stand_gard",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="lum",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="meuble",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="parq",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="poutre",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="velo",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                     {
 *                         "in"="query",
 *                         "name"="ver",
 *                         "type"="int",
 *                         "required"=false,
 *                     },
 *                 },
 *                 "responses"={
 *                     "200"={
 *                         "description"="Raw response from KelQuartier API.",
 *                     },
 *                 },
 *             },
 *             "pagination_enabled"=false,
 *         },
 *     },
 *     itemOperations={
 *     },
 *     graphql={
 *         "item_query",
 *     }
 * )
 */
class Estimation
{
    /**
     * @var string
     */
    public $adr;

    /**
     * @var string
     */
    public $lon;

    /**
     * @var string
     */
    public $lat;

    /**
     * @var string
     */
    public $format;

    /**
     * @var int
     */
    public $type;

    /**
     * @var int
     */
    public $vue;

    /**
     * @var int
     */
    public $surf;

    /**
     * @var int
     */
    public $terrain;

    /**
     * @var int
     */
    public $etg;

    /**
     * @var int
     */
    public $nb_etg; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $annee_constr; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $dpe;

    /**
     * @var int
     */
    public $nb_p; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $nb_c; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $nb_sdb; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $surf_balc_terra; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $asc;

    /**
     * @var int
     */
    public $park;

    /**
     * @var int
     */
    public $cave;

    /**
     * @var int
     */
    public $pisc;

    /**
     * @var int
     */
    public $tennis;

    /**
     * @var int
     */
    public $r_a_n; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_a_p; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_peint; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_elec; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_plomb; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_sol; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_cuis; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $r_sdb; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $bain_douc; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $balc_terra; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $bbc;

    /**
     * @var int
     */
    public $chauf;

    /**
     * @var int
     */
    public $clim;

    /**
     * @var int
     */
    public $cuis;

    /**
     * @var int
     */
    public $cuis_eq; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $exp;

    /**
     * @var int
     */
    public $imm_stand_gard; // @codingStandardsIgnoreLine

    /**
     * @var int
     */
    public $lum;

    /**
     * @var int
     */
    public $meuble;

    /**
     * @var int
     */
    public $parq;

    /**
     * @var int
     */
    public $poutre;

    /**
     * @var int
     */
    public $velo;

    /**
     * @var int
     */
    public $ver;
}
