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
            mkdir($this->tmpDir, 700, true);
        }


    }

    ////
    // actions
    ////
//
//    public function fileDataJson($file, $entityFileName, $widthParent = false) {
//        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
//
//        $data = array(
//            'id' => $file->getId(),
//            'fileName' => $file->getFilename(),
//            'fileExtension' => $ext,
//            'fileType' => $file->getType(),
//            'isPrivate' => $file->getIsPrivate(),
//            'url' => $this->getFileLocationPrivate($file->getId(), $entityFileName)
//        );
//        $type = $this->getType($file->getType());
//        if (count($type) > 0) {
//            $data['actionList']['Action'] = $this->router->generate('kitpages_file_actionOnFile_widgetEmpty');
//            foreach($type as $action=>$actionInfo) {
//                if ($actionInfo['url'] != null) {
//                    $data['actionList'][$action] = $this->router->generate($actionInfo['url']);
//                } else {
//                    $data['actionList'][$action] = $this->router->generate(
//                        'kitpages_file_actionOnFile_widget',
//                        array(
//                            'entityFileName' => $entityFileName,
//                            'typeFile' => $file->getType(),
//                            'actionFile' => $action
//                        )
//                    );
//                }
//            }
//        }
//
//        if ($widthParent && $file->getParent() instanceof FileInterface) {
//            $data['fileParent'] = $this->fileDataJson($file->getParent(), $entityFileName);
//            $data['publishParent'] = $file->getPublishParent();
//        } else {
//            $data['fileParent'] = null;
//            $data['publishParent'] = null;
//        }
//
//       return $data;
//    }



//    public function createFormLocale(
//        $tempFilePath,
//        $fileName,
//        $entityFileName,
//        $itemClass = null,
//        $itemId = null,
//        $fileParent = null,
//        $publishParent = false
//    ) {
//        // send on event
//        $event = new FileEvent();
//        $event->set('tempFilePath', $tempFilePath);
//        $event->set('fileName', $fileName);
//
//        $finfo = finfo_open(FILEINFO_MIME_TYPE);
//        $mimeType = finfo_file($finfo, $tempFilePath);
//        finfo_close($finfo);
//
//        $fileInfo = $this->fileInfo($tempFilePath, $mimeType);
//
//        // the parent file is always the original
//        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId, $fileInfo);
//        if ($fileParent != null && $fileParent instanceof FileInterface  ) {
//            $fileParentParent = $fileParent->getParent();
//            if ($fileParentParent != null && $fileParentParent instanceof FileInterface) {
//                $file->setParent($fileParentParent);
//                $file->setPublishParent($publishParent);
//            } else {
//                $file->setParent($fileParent);
//                $fileParent->setPublishParent($publishParent);
//            }
//        }
//
//
//        $event->set('fileObject', $file);
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileCreateFormLocale, $event);
//        // default action (upload)
//        if (! $event->isDefaultPrevented()) {
//            // manage object creation
//            $file = $event->get('fileObject');
//            $em = $this->getDoctrine()->getEntityManager();
//            $em->persist($file);
//
//            $em->flush();
//
//            // manage upload
//            if (
//                $this->fileSystem->moveTempToAdapter(
//                    $tempFilePath,
//                    new AdapterFile($this->getFilePath($file))
//                )
//            ) {
//                $file->setHasUploadFailed(false);
//            }
//            else {
//                $file->setHasUploadFailed(true);
//            }
//            $em->flush();
//        }
//        // send after event
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileCreateFormLocale, $event);
//        return $file;
//    }


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

//    public function upload($uploadFileName, $fileName, $entityFileName, $itemClass = null, $itemId = null) {
//        // send on event
//        $event = new FileEvent();
//        $event->set('tempFileName', $uploadFileName);
//        $event->set('fileName', $fileName);
//
//        $finfo = finfo_open(FILEINFO_MIME_TYPE);
//        $mimeType = finfo_file($finfo, $uploadFileName);
//        finfo_close($finfo);
//
//        $fileInfo = $this->fileInfo($uploadFileName, $mimeType);
//
//        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId, $fileInfo);
//
//        $event->set('fileObject', $file);
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUpload, $event);
//        // default action (upload)
//        if (! $event->isDefaultPrevented()) {
//            // manage object creation
//            $file = $event->get('fileObject');
//            $em = $this->getDoctrine()->getEntityManager();
//            $em->persist($file);
//
//            $em->flush();
//
//            // manage upload
//            $tempFilePath = tempnam($this->getTmpDir(), $file->getId());
//
//
//            move_uploaded_file($uploadFileName, $tempFilePath);
//            if (
//                $this->fileSystem->moveTempToAdapter(
//                    $tempFilePath,
//                    new AdapterFile($this->getFilePath($file)),
//                    $mimeType
//                )
//            ) {
//                $file->setHasUploadFailed(false);
//            }
//            else {
//                $file->setHasUploadFailed(true);
//            }
//            unlink($tempFilePath);
//            $em->flush();
//        }
//        // send after event
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileUpload, $event);
//        return $file;
//    }
//
//    public function delete(FileInterface $file)
//    {
//        $event = new FileEvent();
//        $event->set('fileObject', $file);
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileDelete, $event);
//        if (!$event->isDefaultPrevented()) {
//            // remove original file
//            $this->fileSystem->unlink(new AdapterFile($this->getFilePath($file)));
//
//            $em = $this->getDoctrine()->getEntityManager();
//            $em->remove($file);
//            $em->flush();
//        }
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileDelete, $event);
//    }
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
//    public function unpublish($filePath, $private)
//    {
//        $event = new FileEvent();
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUnpublish, $event);
//        if (!$event->isDefaultPrevented()) {
//            // remove publish file
//            $this->fileSystem->unlink(new AdapterFile($filePath, $private));
//            if (!$private){
//                $this->fileSystem->rmdirr(new AdapterFile(dirname($filePath), $private));
//            }
//
//        }
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileUnpublish, $event);
//    }
//
//    public function publish(FileInterface $file)
//    {
//        $event = new FileEvent();
//        $event->set('fileObject', $file);
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFilePublish, $event);
//        if (!$event->isDefaultPrevented() && !$file->getIsPrivate()) {
//            $filePublic = new AdapterFile($this->getFilePath($file, false), false);
//            if (!$this->fileSystem->isFile($filePublic)) {
//                $this->fileSystem->copy(
//                    new AdapterFile($this->getFilePath($file)),
//                    $filePublic
//                );
//            }
//
//            if ($file->getPublishParent()) {
//                $fileParent = $file->getParent();
//                if ($fileParent instanceof FileInterface) {
//                    $this->publish($fileParent);
//                }
//            }
//
//        }
//        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFilePublish, $event);
//    }
//
//    public function privateFileExist($nameFile) {
//        return true;
//    }
//
//    public function getFilePath(FileInterface $file, $private = true)
//    {
//        $idString = (string) $file->getId();
//        if (strlen($idString)== 1) {
//            $idString = '0'.$idString;
//        }
//        $dir = substr($idString, 0, 2);
//        $originalDir = $this->getDataDirPrefix(null, $file).$dir;
//
//        if ($private) {
//            $fileName = $originalDir.'/'.$file->getId().'-'.$file->getFilename();
//            return $fileName;
//        } else {
//            return $originalDir.'/'.$file->getId()."/".$file->getFileName();
//        }
//    }
//
//    public function getFileLocationPublic(FileInterface $file)
//    {
//        $private = false;
//        return $this->fileSystem->getFileLocation(new AdapterFile($this->getFilePath($file, $private), $private));
//    }
//
//    public function getFileLocationPrivate($id, $entityFileName = null){
//
//        $parameterList = array('id' => $id);
//        if ($entityFileName != null) {
//            $parameterList['entityFileName'] = $entityFileName;
//        }
//        return $this->router->generate('kitpages_file_render', $parameterList);
//    }

}
