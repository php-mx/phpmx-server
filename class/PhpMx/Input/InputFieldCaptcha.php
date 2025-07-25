<?php

namespace PhpMx\Input;

use PhpMx\Cif;
use PhpMx\Mx5;
use Throwable;

class InputFieldCaptcha extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->required(true, 'Informe o código de validação');

        $this->validate(function ($recived) {
            try {
                $captcha = explode('|', $recived);
                $key = Cif::off($captcha[0]);
                $value = mx5(strtoupper($captcha[1]));
                return Mx5::compare($key, $value);
            } catch (Throwable $e) {
                return false;
            }
        }, 'Código de validação incorreto');
    }
}
