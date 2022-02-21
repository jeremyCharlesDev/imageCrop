<?php

echo '<pre>';
var_dump($_FILES);
echo '</pre>';

$emplacement_temporaire = $_FILES['myphoto']['tmp_name'];
$filename = $_FILES['myphoto']['name'];
$emplacement_destination = "img/". $filename;

$taille_maxi = 800000; //800ko
$taille = $_FILES['myphoto']['size'];
$extensions = array('png','jpg','jpeg');
$ext = new SplFileInfo($_FILES['myphoto']['name']);
$extension = $ext->getExtension();


if(!isset($erreur)){
    //On formate le nom du fichier ici...
    $filename = strtr($filename,
    'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
    'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
    $filename = preg_replace('/([^.a-z0-9]+)/i', '-', $filename); 
    $upload = move_uploaded_file($emplacement_temporaire, $emplacement_destination);
} else {
    $upload = false;
}

if ($upload){
    $message = "Upload réussi" ;
}

$width = 1140;
$height = 600;
$folderPathImg = 'img';

resizeImg($filename, $folderPathImg, $width, $height);
resizeImg($filename, $folderPathImg, 355, 165, true);


/**
 * Fonction qui redimensionne et recadre une image
 *
 * @param [type] $filename / Nom du fichier uploader
 * @param [type] $folderPathImg / Chemin de destination du fichier
 * @param [type] $desired_width / Largeur désiré
 * @param [type] $desired_height / Hauteur désiré
 * @param boolean $isMobile / Change le nom du fichier selon la valeur
 * @return void
 */
function resizeImg($filename, $folderPathImg, $desired_width, $desired_height, $isMobile = false){

    // Ajoute un préfix au nom de l'image selon la valeur de $isMobile
    $prefix = $isMobile ? "_thumbnail" : "_medium";
    $ext = new SplFileInfo($filename);
    $extension = $ext->getExtension();
    $newFileName = pathinfo($filename, PATHINFO_FILENAME).$prefix.".".$extension;
    $imageOutput = $folderPathImg."/".$filename;
    $type = exif_imagetype($folderPathImg."/".$filename);

    // Calcul des nouvelles dimensions
    list($width, $height) = getimagesize($folderPathImg."/".$filename);

    // Redimensionne en gardant le ratio de l'image
    // par défaut redimensionne l'image en X selon $desired_with 
    // Si la hauteur n'est pas suffisante, l'image est redimensionné en Y selon $desired_height
    $ratio = $width/$desired_width;
    $new_height = ceil($height / $ratio);
    $new_width = $desired_width;
    if($new_height < $desired_height){
        $ratio = $height / $desired_height;
        $new_width = ceil($width / $ratio);
        $new_height = $desired_height;
    }
    // Redimensionnement
    $new_image = imagecreatetruecolor($new_width, $new_height);

    if( $type == IMAGETYPE_PNG ) {    
        imagealphablending($new_image, false);
        $transparency = imagecolorallocatealpha($new_image,255,255,255, 0);
        imagefilledrectangle($new_image,0,0, $new_width, $new_height, $transparency);
        imagealphablending($new_image,true);
    }

    $image = loadImageCreate($imageOutput, $type);
    // $image = imagecreatefromjpeg($imageOutput);

    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Affichage
    $imageOutput = $folderPathImg."/".$newFileName;    
    copyImage($new_image, $imageOutput, $type, 70);
    // imagejpeg($new_image, $imageOutput, 70);


    /////////////////////////////////////////////////////////////////////////
    // CROP la nouvelle image qui à été redimensionné (à partir de son centre)
    /////////////////////////////////////////////////////////////////////////
    // $image = imagecreatefromjpeg($folderPathImg."/".$newFileName);
    $image = loadImageCreate($imageOutput, $type);
    if($new_width != $desired_width){
        $x = (int) ($new_width - $desired_width) / 2;
        $y = 0;
    } else {
        $y = (int) ($new_height - $desired_height) / 2;
        $x = 0;
    }
    $imgCrop = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $desired_width, 'height' => $desired_height]);
    if ($imgCrop !== FALSE) {
        copyImage($imgCrop, $imageOutput, $type, 100);
        // imagejpeg($imgCrop, $imageOutput, 100);
    }
}

function loadImageCreate($filename, $type) {
    if( $type == IMAGETYPE_JPEG ) {
        $image = imagecreatefromjpeg($filename);
    }
    elseif( $type == IMAGETYPE_PNG ) {    
        $image = imagecreatefrompng($filename);
    }
    elseif( $type == IMAGETYPE_GIF ) {
        $image = imagecreatefromgif($filename);
    }
    return $image;
}

function copyImage($image, $imageOutput, $type, $quality) {
    if( $type == IMAGETYPE_JPEG ) {
        imagejpeg($image, $imageOutput, $quality);
    }
    elseif( $type == IMAGETYPE_PNG ) {
        imagepng($image, $imageOutput, 9);
    }
    elseif( $type == IMAGETYPE_GIF ) {
        imagegif($image, $imageOutput);
    }
}


// function cropImg($filename, $new_width, $new_height) {
//     $pathImg = 'img/';
//     $image = imagecreatefromjpeg($pathImg.$filename);
//     // $size = min(imagesx($image), imagesy($image));
  
//     // Set the crop image size 
//     $im2 = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $new_width, 'height' => $new_height]);
//     if ($im2 !== FALSE) {
//         $imageOutput = $pathImg.$filename;
//         imagejpeg($im2, $imageOutput, 100);
//     }
// }

header('location: index.php');


