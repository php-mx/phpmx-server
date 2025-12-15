<?php

namespace PhpMx;

abstract class Context
{
    public ?Request $request;
    public ?Response $response;
}
