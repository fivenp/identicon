<?php
namespace Fivenp\Identicon;
class Identicon {
    var $palette = array(
        'lightBlack' => '#2c2c2c',
        'lightBlackIntense' => '#232323',
        'turquoise' => '#00bf93',
        'turquoiseIntense' => '#16a086',
        'mint' => '#2dcc70',
        'mintIntense' => '#27ae61',
        'green' => '#42e453',
        'greenIntense' => '#24c333',
        'yellow' => '#ffff25',
        'yellowIntense' => '#d9d921',
        'yellowOrange' => '#f1c40f',
        'yellowOrangeIntense' => '#f39c11',
        'brown' => '#e67f22',
        'brownIntense' => '#d25400',
        'orange' => '#ff944e',
        'orangeIntense' => '#ff5500',
        'red' => '#e84c3d',
        'redIntense' => '#c1392b',
        'blue' => '#3598db',
        'blueIntense' => '#297fb8',
        'darkBlue' => '#34495e',
        'darkBlueIntense' => '#2d3e50',
        'lightGrey' => '#ecf0f1',
        'lightGreyIntense' => '#bec3c7',
        'grey' => '#95a5a5',
        'greyIntense' => '#7e8c8d',
        'magenta' => '#ef3e96',
        'magentaIntense' => '#e52383',
        'violet' => '#df21b9',
        'violetIntense' => '#be127e',
        'purple' => '#9a59b5',
        'purpleIntense' => '#8d44ad',
        'lightBlue' => '#7dc2d2',
        'lightBlueIntense' => '#1cabbb',
        'black' => '#000000',
    );
    var $availableBackgroundColors = array(
        'lightGrey',
        'darkBlueIntense',
        'lightBlack',
    );

    var $size = 1024;
    var $backgroundColor = array(
        "red" => "30",
        "green" => "30",
        "blue" => "30",
    );
    var $spriteZ=512;

    /**
     * class constructor
     * @param string $hash
     * @param array $options
     */
    function __construct($hash=null, $options=array()) {
        $this->hash = $hash;
        if (!$this->hash){
            $this->hash = md5(uniqid(time(), true));
        }
        if (array_key_exists('backgroundColor', $options)) {
            $this->backgroundColor = $options['backgroundColor'];
        } else {
            $this->backgroundColor = $this->randomBackgroundColor();
        }
        // size is between 16 and 2048
        if (array_key_exists('size', $options)) {
            $this->size = max(16, min(intval($options['size']), 2048));
        }

    }

    public function create(){
        $hash = $this->hash;
        $size = $this->size;
        $palette = $this->palette;
        $spriteZ = $this->spriteZ;

        $csh=hexdec(substr($hash,0,1)); // corner sprite shape
        $ssh=hexdec(substr($hash,1,1)); // side sprite shape
        $xsh=hexdec(substr($hash,2,1))&7; // center sprite shape

        $cro=hexdec(substr($hash,3,1))&3; // corner sprite rotation
        $sro=hexdec(substr($hash,4,1))&3; // side sprite rotation
        $xbg=hexdec(substr($hash,5,1))%2; // center sprite background

        /* corner sprite foreground color */
        // $cfr=hexdec(substr($hash,6,2));
        // $cfg=hexdec(substr($hash,8,2));
        // $cfb=hexdec(substr($hash,10,2));
        $fgColor = $this->randomColorFromPalette();
        $cfr=$fgColor["red"];
        $cfg=$fgColor["green"];
        $cfb=$fgColor["blue"];

        /* side sprite foreground color */
        // $sfr=hexdec(substr($hash,12,2));
        // $sfg=hexdec(substr($hash,14,2));
        // $sfb=hexdec(substr($hash,16,2));
        $fgColor = $this->randomColorFromPalette();
        $sfr=$fgColor["red"];
        $sfg=$fgColor["green"];
        $sfb=$fgColor["blue"];

        /* final angle of rotation */
        $angle=hexdec(substr($hash,18,2));

        /* start with blank 3x3 identicon */
        $identicon=imagecreatetruecolor($spriteZ*3,$spriteZ*3);
        imageantialias($identicon,TRUE);

        /* assign white as background */
        //$bg=imagecolorallocate($identicon,255,255,255);
        $bgColor = $this->randomColorFromPalette();
        $bg=imagecolorallocate($identicon,$bgColor["red"],$bgColor["green"],$bgColor["blue"]);
        imagefilledrectangle($identicon,0,0,$spriteZ,$spriteZ,$bg);

        /* generate corner sprites */
        $corner=$this->getsprite($csh,$cfr,$cfg,$cfb,$cro);
        imagecopy($identicon,$corner,0,0,0,0,$spriteZ,$spriteZ);
        $corner=imagerotate($corner,90,$bg);
        imagecopy($identicon,$corner,0,$spriteZ*2,0,0,$spriteZ,$spriteZ);
        $corner=imagerotate($corner,90,$bg);
        imagecopy($identicon,$corner,$spriteZ*2,$spriteZ*2,0,0,$spriteZ,$spriteZ);
        $corner=imagerotate($corner,90,$bg);
        imagecopy($identicon,$corner,$spriteZ*2,0,0,0,$spriteZ,$spriteZ);

        /* generate side sprites */
        $side=$this->getsprite($ssh,$sfr,$sfg,$sfb,$sro);
        imagecopy($identicon,$side,$spriteZ,0,0,0,$spriteZ,$spriteZ);
        $side=imagerotate($side,90,$bg);
        imagecopy($identicon,$side,0,$spriteZ,0,0,$spriteZ,$spriteZ);
        $side=imagerotate($side,90,$bg);
        imagecopy($identicon,$side,$spriteZ,$spriteZ*2,0,0,$spriteZ,$spriteZ);
        $side=imagerotate($side,90,$bg);
        imagecopy($identicon,$side,$spriteZ*2,$spriteZ,0,0,$spriteZ,$spriteZ);

        /* generate center sprite */
        $center=$this->getcenter($xsh,$cfr,$cfg,$cfb,$sfr,$sfg,$sfb,$xbg);
        imagecopy($identicon,$center,$spriteZ,$spriteZ,0,0,$spriteZ,$spriteZ);

        // $identicon=imagerotate($identicon,$angle,$bg);

        /* make white transparent */
        // imagecolortransparent($identicon,$bg);

        /* create blank image according to specified dimensions */
        $resized=imagecreatetruecolor($size,$size);
        imageantialias($resized,TRUE);

        /* assign white as background */
        // $bg=imagecolorallocate($resized,255,255,255);

        $bgColor = $this->randomColorFromPalette();
        $bg=imagecolorallocate($resized,$bgColor["red"],$bgColor["green"],$bgColor["blue"]);
        imagefilledrectangle($resized,0,0,$size,$size,$bg);

        /* resize identicon according to specification */
        imagecopyresampled($resized,$identicon,0,0,(imagesx($identicon)-$spriteZ*3)/2,(imagesx($identicon)-$spriteZ*3)/2,$size,$size,$spriteZ*3,$spriteZ*3);

        /* make white transparent */
        // imagecolortransparent($resized,$bg);

        /* and finally, send to standard output */
        header("Content-Type: image/png");
        imagepng($resized);
    }

    /* generate sprite for corners and sides */
    private function getsprite($shape,$R,$G,$B,$rotation) {
        $randomBackgroundColor = $this->backgroundColor;
        $spriteZ = $this->spriteZ;

        $sprite=imagecreatetruecolor($spriteZ,$spriteZ);
        imageantialias($sprite,TRUE);
        $fg=imagecolorallocate($sprite,$R,$G,$B);
        $bg=imagecolorallocate($sprite,$randomBackgroundColor['red'],$randomBackgroundColor['green'],$randomBackgroundColor['blue']);
        imagefilledrectangle($sprite,0,0,$spriteZ,$spriteZ,$bg);
        switch($shape) {
            case 0: // triangle
                $shape=array(
                    0.5,1,
                    1,0,
                    1,1
                );
                break;
            case 1: // parallelogram
                $shape=array(
                    0.5,0,
                    1,0,
                    0.5,1,
                    0,1
                );
                break;
            case 2: // mouse ears
                $shape=array(
                    0.5,0,
                    1,0,
                    1,1,
                    0.5,1,
                    1,0.5
                );
                break;
            case 3: // ribbon
                $shape=array(
                    0,0.5,
                    0.5,0,
                    1,0.5,
                    0.5,1,
                    0.5,0.5
                );
                break;
            case 4: // sails
                $shape=array(
                    0,0.5,
                    1,0,
                    1,1,
                    0,1,
                    1,0.5
                );
                break;
            case 5: // fins
                $shape=array(
                    1,0,
                    1,1,
                    0.5,1,
                    1,0.5,
                    0.5,0.5
                );
                break;
            case 6: // beak
                $shape=array(
                    0,0,
                    1,0,
                    1,0.5,
                    0,0,
                    0.5,1,
                    0,1
                );
                break;
            case 7: // chevron
                $shape=array(
                    0,0,
                    0.5,0,
                    1,0.5,
                    0.5,1,
                    0,1,
                    0.5,0.5
                );
                break;
            case 8: // fish
                $shape=array(
                    0.5,0,
                    0.5,0.5,
                    1,0.5,
                    1,1,
                    0.5,1,
                    0.5,0.5,
                    0,0.5
                );
                break;
            case 9: // kite
                $shape=array(
                    0,0,
                    1,0,
                    0.5,0.5,
                    1,0.5,
                    0.5,1,
                    0.5,0.5,
                    0,1
                );
                break;
            case 10: // trough
                $shape=array(
                    0,0.5,
                    0.5,1,
                    1,0.5,
                    0.5,0,
                    1,0,
                    1,1,
                    0,1
                );
                break;
            case 11: // rays
                $shape=array(
                    0.5,0,
                    1,0,
                    1,1,
                    0.5,1,
                    1,0.75,
                    0.5,0.5,
                    1,0.25
                );
                break;
            case 12: // double rhombus
                $shape=array(
                    0,0.5,
                    0.5,0,
                    0.5,0.5,
                    1,0,
                    1,0.5,
                    0.5,1,
                    0.5,0.5,
                    0,1
                );
                break;
            case 13: // crown
                $shape=array(
                    0,0,
                    1,0,
                    1,1,
                    0,1,
                    1,0.5,
                    0.5,0.25,
                    0.5,0.75,
                    0,0.5,
                    0.5,0.25
                );
                break;
            case 14: // radioactive
                $shape=array(
                    0,0.5,
                    0.5,0.5,
                    0.5,0,
                    1,0,
                    0.5,0.5,
                    1,0.5,
                    0.5,1,
                    0.5,0.5,
                    0,1
                );
                break;
            default: // tiles
                $shape=array(
                    0,0,
                    1,0,
                    0.5,0.5,
                    0.5,0,
                    0,0.5,
                    1,0.5,
                    0.5,1,
                    0.5,0.5,
                    0,1
                );
                break;
        }
        /* apply ratios */
        for ($i=0;$i<count($shape);$i++)
            $shape[$i]=$shape[$i]*$spriteZ;
        imagefilledpolygon($sprite,$shape,count($shape)/2,$fg);
        /* rotate the sprite */
        for ($i=0;$i<$rotation;$i++)
            $sprite=imagerotate($sprite,90,$bg);
        return $sprite;
    }

    /* generate sprite for center block */
    private function getcenter($shape,$fR,$fG,$fB,$bR,$bG,$bB,$usebg) {
        $spriteZ = $this->spriteZ;
        $sprite=imagecreatetruecolor($spriteZ,$spriteZ);
        imageantialias($sprite,TRUE);
        $fg=imagecolorallocate($sprite,$fR,$fG,$fB);
        /* make sure there's enough contrast before we use background color of side sprite */
        if ($usebg>0 && (abs($fR-$bR)>127 || abs($fG-$bG)>127 || abs($fB-$bB)>127))
            $bg=imagecolorallocate($sprite,$bR,$bG,$bB);
        else
            $bg=imagecolorallocate($sprite,33,33,33);
        imagefilledrectangle($sprite,0,0,$spriteZ,$spriteZ,$bg);
        switch($shape) {
            case 0: // empty
                $shape=array();
                break;
            case 1: // fill
                $shape=array(
                    0,0,
                    1,0,
                    1,1,
                    0,1
                );
                break;
            case 2: // diamond
                $shape=array(
                    0.5,0,
                    1,0.5,
                    0.5,1,
                    0,0.5
                );
                break;
            case 3: // reverse diamond
                $shape=array(
                    0,0,
                    1,0,
                    1,1,
                    0,1,
                    0,0.5,
                    0.5,1,
                    1,0.5,
                    0.5,0,
                    0,0.5
                );
                break;
            case 4: // cross
                $shape=array(
                    0.25,0,
                    0.75,0,
                    0.5,0.5,
                    1,0.25,
                    1,0.75,
                    0.5,0.5,
                    0.75,1,
                    0.25,1,
                    0.5,0.5,
                    0,0.75,
                    0,0.25,
                    0.5,0.5
                );
                break;
            case 5: // morning star
                $shape=array(
                    0,0,
                    0.5,0.25,
                    1,0,
                    0.75,0.5,
                    1,1,
                    0.5,0.75,
                    0,1,
                    0.25,0.5
                );
                break;
            case 6: // small square
                $shape=array(
                    0.33,0.33,
                    0.67,0.33,
                    0.67,0.67,
                    0.33,0.67
                );
                break;
            case 7: // checkerboard
                $shape=array(
                    0,0,
                    0.33,0,
                    0.33,0.33,
                    0.66,0.33,
                    0.67,0,
                    1,0,
                    1,0.33,
                    0.67,0.33,
                    0.67,0.67,
                    1,0.67,
                    1,1,
                    0.67,1,
                    0.67,0.67,
                    0.33,0.67,
                    0.33,1,
                    0,1,
                    0,0.67,
                    0.33,0.67,
                    0.33,0.33,
                    0,0.33
                );
                break;
        }
        /* apply ratios */
        for ($i=0;$i<count($shape);$i++)
            $shape[$i]=$shape[$i]*$spriteZ;
        if (count($shape)>0)
            imagefilledpolygon($sprite,$shape,count($shape)/2,$fg);
        return $sprite;
    }

    private function randomColorFromPalette(){
        $k = array_rand($this->palette);
        return($this->hex2RGB($this->palette[$k]));
    }
    private function randomBackgroundColor(){
        $k = array_rand($this->availableBackgroundColors);
        return($this->hex2RGB($this->palette[$this->availableBackgroundColors[$k]]));
    }

    private function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; //Invalid hex color code
        }
        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }
}
?>
