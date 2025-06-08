<?php

namespace Controller\Base;

use PhpMx\Cif;
use PhpMx\Code;
use PhpMx\Mime;
use PhpMx\Path;

class Captcha
{
    function default()
    {
        $captcha = prepare("[#][#][#][#][#][#]", [
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1),
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1),
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1),
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1),
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1),
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXZWY1234567890"), 0, 1)
        ]);

        return [
            'key' => $this->getKey($captcha),
            'image' => $this->getImage($captcha),
        ];
    }

    protected function getKey($captcha): string
    {
        return Cif::on(Code::on($captcha));
    }

    protected function getImage($captcha): string
    {
        $image = imagecreate(175, 50);
        imagecolorallocate($image, 21, 20, 36);
        $color = imagecolorallocate($image, 255, 200, 82);

        $fontFile = Path::seekFile('storage/font/arial.ttf');

        for ($i = 1; $i <= 7; $i++) {
            imagettftext(
                $image,
                18,
                rand(-25, 25),
                (22 * $i),
                (22 + 12),
                $color,
                $fontFile,
                substr($captcha, ($i - 1), 1)
            );
        }

        ob_start();

        imagejpeg($image);

        $bin = ob_get_contents();

        ob_end_clean();

        $type = Mime::getMimeEx('jpg');
        $b64 = base64_encode($bin);
        return "data:$type;base64,$b64";
    }
}
