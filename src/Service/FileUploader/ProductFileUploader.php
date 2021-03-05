<?php

namespace App\Service\FileUploader;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Handles Main Picture of a product send by user on server
 */
class ProductFileUploader
{
    private $targetDirectory;    
    /**
     * slugger
     *
     * @var SluggerInterface
     */
    private $slugger;

    private $flashBag;
    

    public function __construct($targetDirectory, SluggerInterface $slugger, FlashBagInterface $flashBag)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->flashBag = $flashBag;
    }
    
    /**
     * upload a file
     *
     * @param  UploadedFile $file
     * @return string $filename
     */
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            $this->flashBag->add("danger", "Erreur lors du téléchargement de l'image:".$e);
        }
        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}