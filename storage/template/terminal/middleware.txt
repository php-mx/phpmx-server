<?php

return new class {

    function __invoke(Closure $next)
    {
        return $next();
    }
};
