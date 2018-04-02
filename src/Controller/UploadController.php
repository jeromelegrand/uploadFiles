<?php
/**
 * Created by PhpStorm.
 * User: wilder16
 * Date: 02/04/18
 * Time: 21:41
 */

namespace Wcs\Controller;


class UploadController extends AbstractController
{
    public function index()
    {
        //récupération des informations sur les fichiers du dossier upload
        $scanUploads = scandir(UPLOAD_DIR);
        unset($scanUploads[array_search('.', $scanUploads)]);
        unset($scanUploads[array_search('..', $scanUploads)]);


        if (count($scanUploads) === 0) {
            echo $this->twig->render('index.html.twig', [
                'files' => $this->files,
                'errors' => $this->errors,
            ]);
        } else {

            foreach ($scanUploads as $upload) {
                $file = [];
                $file['name'] = $upload;
                $file['image'] = 'upload/' . $upload;
                $this->uploads[] = $file;
            }

            echo $this->twig->render('index.html.twig', [
                'files' => $this->files,
                'errors' => $this->errors,
                'uploads' => $this->uploads,
            ]);
        }
    }

    public function add()
    {
        $files = $_FILES['files'];

        if ($files['error'][0] === 4) {
            $this->errors[] = 'Il faut sélectionner au moins 1 fichier.';
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
                $infoName = pathinfo($file['name']);
                $extension = '.' . $infoName['extension'];
                $file['upload_dir'] = UPLOAD_DIR . 'image' . uniqid() . $extension;
                $uploadFiles[] = $file;
            }

            //tests sur les fichiers
            foreach ($uploadFiles as $uploadFile) {
                $error = false;
                if ($uploadFile['size'] > 1024000) {
                    $this->errors[] = 'Le fichier ' . $file['name'] . ' est trop volumineux.';
                    $error = true;
                }

                if (!in_array($uploadFile['type'], ['image/gif', 'image/jpeg', 'image/png'])) {
                    $this->errors[] = 'Le type du fichier n\'est pas jpg, png ou gif.';
                    $error = true;
                }

                if (!$error) {
                    move_uploaded_file($uploadFile['tmp_name'], $uploadFile['upload_dir']);
                }
            }
        }

        $this->index();
    }

    public function delete()
    {
        if (isset($_POST['delete'])) {
            $fileToDelete = 'upload/' . $_POST['delete'];
            echo $fileToDelete;
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        }

        header('Location: /');
    }

}
