<?php

namespace App\Services;

use App\Entity\Campus;

class SearchUser
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
     * @var string
     */
    public $isAdmin;

    /**
     * @var string
     */
    public $isActif;
}
