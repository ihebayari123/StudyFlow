<?php
// gd_test.php

if (extension_loaded('gd')) {
    echo "✅ GD est chargé<br>";
    echo "Version GD : " . gd_info()['GD Version'] . "<br>";
    
    // Test de création d'image
    $im = imagecreatetruecolor(100, 50);
    if ($im) {
        echo "✅ Création d'image réussie<br>";
        $text_color = imagecolorallocate($im, 255, 255, 255);
        imagestring($im, 5, 10, 15, "Test OK", $text_color);
        imagepng($im, 'test_image.png');
        imagedestroy($im);
        
        if (file_exists('test_image.png')) {
            echo "✅ Image créée avec succès : <a href='test_image.png'>Voir l'image</a>";
        } else {
            echo "❌ Échec de la sauvegarde de l'image";
        }
    } else {
        echo "❌ Échec de la création d'image";
    }
} else {
    echo "❌ GD n'est PAS chargé !";
}