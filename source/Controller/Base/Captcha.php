<?php

namespace Controller\Base;

use Exception;
use PhpMx\Cif;
use PhpMx\Code;
use PhpMx\Mime;
use PhpMx\Path;

class Captcha
{
    function default($color = '000', $background = 'fff')
    {
        $fg = $color;
        $bg = $background;

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
            'image' => $this->getImage($captcha, $fg, $bg),
        ];
    }

    protected function getKey($captcha): string
    {
        return Cif::on(Code::on($captcha));
    }

    protected function getImage($captcha, $fg, $bg): string
    {
        $fg = $this->getColorRGB($fg);
        $bg = $this->getColorRGB($bg);

        $image = imagecreate(175, 50);
        imagecolorallocate($image, $bg['r'], $bg['g'], $bg['b']);
        $color = imagecolorallocate($image, $fg['r'], $fg['g'], $fg['b']);

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

    protected function getColorRGB(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3)
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];

        if (strlen($hex) !== 6)
            throw new Exception("Invalid hexadecimal color [$hex]");

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
