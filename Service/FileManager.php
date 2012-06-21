<?php

namespace Kitpages\EdmBundle\Service;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Routing\RouterInterface;
use Kitpages\EdmBundle\Entity\File;

use Kitpages\FileSystemBundle\Service\Adapter\AdapterInterface;
use Kitpages\FileSystemBundle\Model\AdapterFile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager {
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $router = null;
    protected $fileSystem = null;
    protected $tmp_dir = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        AdapterInterface $fileSystem,
        $tmp_dir
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->fileSystem = $fileSystem;
        $this->tmpDir = $tmp_dir;

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0700, true);
        }


    }

    public function getFileSystem() {
        return $this->fileSystem;
    }

    ////
    // actions
    ////
    public function fileInfo($file, $mimeType) {

        $fileStat = stat($file);

        $infoList['size'] = $fileStat['7'];
        $infoList['mtime'] = $fileStat['9'];

        $typeList = explode('/', $mimeType);
        if ($typeList[0] == 'image') {
            $imageInfo = getimagesize($file);
            $infoList['width'] = $imageInfo[0];
            $infoList['height'] = $imageInfo[1];
        }
        return $infoList;

    }

    public function createFile(
        $fileName,
        $mimeType,
        $fileInfo
    ) {
        $file = new File();
        $file->setFileName($fileName);
        $file->setStatus(File::FILE_STATUS_CURRENT);
        $typeList = explode('/', $mimeType);
        $file->setType($typeList[0]);
        $file->setMimeType($mimeType);
        $file->setData($fileInfo);

        return $file;
    }

    public function newFile(UploadedFile $uploadedFile) {

        $mimeType = $uploadedFile->getClientMimeType();

        $fileInfo = $this->fileInfo($uploadedFile->getRealPath(), $mimeType);

        $file = $this->createFile($uploadedFile->getClientOriginalName(), $mimeType, $fileInfo);
        $file->setVersion(1);
        $file->setVersionNote('Note auto: file original');

        $em = $this->doctrine->getEntityManager();
        $em->persist($file);
        $em->flush();

        $tempFilePath = tempnam($this->tmpDir, $file->getId());
        $uploadedFile->move($this->tmpDir, basename($tempFilePath));

        if (
            $this->fileSystem->copyTempToAdapter(
                $tempFilePath,
                new AdapterFile($this->getFilePath($file)),
                $mimeType
            )
        ) {
            $file->setHasUploadFailed(false);
        }
        else {
            $file->setHasUploadFailed(true);
        }
        unlink($tempFilePath);
        $em->flush();

        return $file;
    }

    public function newVersionFile(File $oldVersionFile, UploadedFile $uploadedFile, $versionNote) {

        $mimeType = $uploadedFile->getClientMimeType();

        $fileInfo = $this->fileInfo($uploadedFile->getRealPath(), $mimeType);

        $file = $this->createFile($uploadedFile->getClientOriginalName(), $mimeType, $fileInfo);
        $file->setVersion($oldVersionFile->getVersion()+1);
        $file->setVersionNote($versionNote);
        $file->setPreviousVersion($oldVersionFile);
        $fileOriginal = $oldVersionFile->getOriginalVersion();
        if ($fileOriginal instanceof File) {
            $file->setOriginalVersion($fileOriginal);
        } else {
            $file->setOriginalVersion($oldVersionFile);
        }

        $oldVersionFile->setStatus(File::FILE_STATUS_OLD_VERSION);

        $em = $this->doctrine->getEntityManager();
        $em->persist($file);
        $em->persist($oldVersionFile);
        $em->flush();

        $tempFilePath = tempnam($this->tmpDir, $file->getId());
        $uploadedFile->move($this->tmpDir, basename($tempFilePath));

        if (
            $this->fileSystem->copyTempToAdapter(
                $tempFilePath,
                new AdapterFile($this->getFilePath($file)),
                $mimeType
            )
        ) {
            $file->setHasUploadFailed(false);
        }
        else {
            $file->setHasUploadFailed(true);
        }
        unlink($tempFilePath);
        $em->flush();

        return $file;
    }

    public function getFilePath(File $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);

        return $dir.'/'.$file->getId().'-'.$file->getFilename();
    }

    public function modifyStatus(File $file, $status)
    {
        $em = $this->doctrine->getEntityManager();
        $file->setStatus($status);
        $em->persist($file);
        $em->flush();
    }

    public function deleteFile(File $file)
    {
        $this->fileSystem->unlink(new AdapterFile($this->getFilePath($file)));
        if ($file->getPreviousVersion() != null) {
            $this->deleteFile($file->getPreviousVersion());
        }
    }

    public function deleteOldFileVersion(File $file)
    {
        $previousVersion = $file->getPreviousVersion();
        if ($previousVersion != null) {
            $em = $this->doctrine->getEntityManager();
            $em->remove($previousVersion);
            $em->flush();
            $this->deleteFile($previousVersion);
        }
    }

//
//    public function deleteTemp($itemCategory, $itemId, $entityFileName = 'default')
//    {
//        $em = $this->getDoctrine()->getEntityManager();
//        $fileClass = $this->getFileClass($entityFileName);
//        $fileList = $em->getRepository($fileClass)->findByStatusAndItem(
//            FileInterface::STATUS_TEMP,
//            $itemCategory,
//            $itemId
//        );
//        foreach($fileList as $file) {
//            $this->delete($file);
//        }
//    }
//

}
