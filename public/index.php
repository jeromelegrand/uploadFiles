<?php
/**
 * Created by PhpStorm.
 * User: wilder16
 * Date: 29/03/18
 * Time: 15:52
 */

require_once '../vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('../src/View');

$twig = new Twig_Environment($loader, [
    'cache' => false,
    'debug' => true
]);
$twig->addExtension(new Twig_Extension_Debug());


$uploadDir = '../public/upload/';
$files = $errors = $uploads = [];

if (isset($_POST['delete'])) {
    $deleteFile = 'upload/' . $_POST['delete'];
    if (file_exists($deleteFile)) {
        unlink($deleteFile);
    }
}

if (!empty($_FILES)) {
    $files = $_FILES['files'];

    if ($files['error'][0] === 4) {
        $errors[] = 'Il faut sélectionner au moins 1 fichier.';
    } else {
        //traitement des fichiers
        $uploadFiles = [];
        for ($i = 0; $i < count($files['name']); $i++) {
            $file = [];
            $file['name'] = $files['name'][$i];
            $file['type'] = $files['type'][$i];
            $file['tmp_name'] = $files['tmp_name'][$i];
            $file['error'] = $files['error'][$i];
            $file['size'] = $files['size'][$i];
            $file['upload_dir'] = $uploadDir. uniqid() . '_' . basename($file['name']);
            $uploadFiles[] = $file;
        }

        //tests sur les fichiers
        foreach ($uploadFiles as $uploadFile) {
            $error = false;
            if ($uploadFile['size'] > 1024000) {
                $errors[] = 'Le fichier ' . $file['name'] . ' est trop volumineux.';
                $error = true;
            }

            if (!in_array($uploadFile['type'], ['image/gif', 'image/jpeg', 'image/png'])) {
                $errors[] = 'Le type du fichier n\'est pas jpg, png ou gif.';
                $error = true;
            }

            if (!$error) {
                move_uploaded_file($uploadFile['tmp_name'], $uploadFile['upload_dir']);

                //création d'une vignette
                
            }
        }
    }
}

//récupération des informations sur les fichiers du dossier upload
$scanUploads = scandir($uploadDir);
unset($scanUploads[array_search('.', $scanUploads)]);
unset($scanUploads[array_search('..', $scanUploads)]);


if (count($scanUploads) === 0) {
    echo $twig->render('index.html.twig', [
        'files' => $files,
        'errors' => $errors,
    ]);
} else {

    foreach ($scanUploads as $upload) {
        $file = [];
        $file['name'] = $upload;
        $file['image'] = 'upload/' . $upload;
        $uploads[] =  $file;
    }

    echo $twig->render('index.html.twig', [
        'files' => $files,
        'errors' => $errors,
        'uploads' => $uploads,
    ]);
}
