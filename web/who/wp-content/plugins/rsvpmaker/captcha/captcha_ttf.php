<?php
session_start();

// Set the content-type
header("Content-type: image/png");

// Create the image
$im = imagecreatetruecolor(170, 60);

$length=5;
 $possible = '23456789bcdfghjkmnprstvwxyz';
 $i = 0;
 //doublecheck +$string='';
 $string='';
 while ($i < $length) {
 $string .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
 $i++;
 }
/*
Now for the GD stuff, for ease of use lets create
the image from a background image.
*/
$captcha = imagecreatefrompng("captcha_bk.png");
/*
Lets set the colours, the colour $line is used to generate lines.
Using a blue misty colours. The colour codes are in RGB
*/
$color = imagecolorallocate($captcha, 0, 0, 200);
/*
Encrypt and store the key inside of a session
*/
$_SESSION['captcha_key'] = md5($string);


// Create some colors
$bgcolor = imagecolorallocate($im, 200, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$textcolor = imagecolorallocate($im, 255, 0, 0);
imagefilledrectangle($im, 0, 0, 170, 62, $bgcolor);

/*
Now to make it a little bit harder for any bots to break,
assuming they can break it so far. Lets add some lines
in (static lines) to attempt to make the bots life a little harder
*/
function newNum(){
$num=rand(0, 150);
return $num;
}
function randomline($im,$line){
 imageline($im,newNum(),newNum(),newNum(),newNum(),$line);
 }
 for ($i = 0; $i <= rand(40,50); $i++) {
-randomline($im,$line);
+randomline($im,$color);
 }
 
 // The text to draw
 $text = $string; //'Testing...';
 // Replace path by your own font path
-$font = 'Acidic.ttf';
+$font = './Acidic.ttf';

// Add the text
imagettftext($im, 40, 5, 3, 50, $textcolor, $font, $text);

// Using imagepng() results in clearer text compared with imagejpeg()
imagepng($im);
imagedestroy($im);
?>
