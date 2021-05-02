<?php

namespace App\Services;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\User;
use DateTime;

class SearchSortie
{

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $keyword = '';

    /**
     * @var Campus
     */
    public $campus;

    /**
     * @var null|DateTime
     */
    public $dateMin;

    /**
     * @var null|DateTime
     */
    public $dateMax;

    /**
     * @var boolean
     */
    public $archive = false;

    /**
     * @var User
     */
    public $organisateur;

    /**
     * @var Etat
     */
    public $etat;

}
