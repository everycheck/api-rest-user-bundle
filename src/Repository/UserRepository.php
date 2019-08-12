<?php

namespace EveryCheck\UserApiRestBundle\Repository;

use EveryCheck\ApiRest\Utils\PaginatedRepositoryTrait;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
	use PaginatedRepositoryTrait;

    const BASE_QUERY_NAME = 'user';
    const LEFT_JOIN_ALIAS_LIST = [
    ];
    const FILTER_OPTION = [
    ];

}