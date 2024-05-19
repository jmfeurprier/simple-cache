<?php

namespace Jmf\SimpleCache\Exception;

use Exception;
use Psr\SimpleCache\CacheException as PsrCacheException;

class CacheException extends Exception implements PsrCacheException
{
}
