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
     * @var boolean
     */
    public $isAdmin = false;

    /**
     * @var boolean
     */
    public $isActif = true;
}
