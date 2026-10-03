<?php


// $url = $this->pathFile . $fileName;
// move_uploaded_file($file['tmp_name'], $url);
// PDF=> application/pdf
// PPT=> application/vnd.openxmlformats-officedocument.presentationml.presentation
// EXCEL => application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
// TXT => text/plain
// CSV => application/vnd.ms-excel

class Upload
{
    public $pathImage, $pathFile, $pathCV, $pathBusiness, $pathPostulant;
    public function __construct()
    {
        $this->pathImage = "private/image/image_upload/";
        $this->pathFile = "private/file/file_upload/";
        $this->pathCV = "private/cv/";
        $this->pathBusiness = "private/business/";
        $this->pathPostulant = "private/postulant/";
    }

    public function upload_basic($dataForm, $path, $name_file)
    {
        $file = $dataForm; //JSON con todos los datos del archivo seleccionado
        $fileName = $name_file; //nombre del archivo
        $mimetype = $file['type']; //tipo de archivo

        $type_image = array("image/jpg", "image/jpeg", "image/png");
        $type_file = array("application/pdf", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "text/plain", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-excel", "application/x-pkcs12");

        if (in_array($mimetype, $type_image)) { //IMAGEN
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
            $url = $path . $fileName;
            move_uploaded_file($file['tmp_name'], $url);

            return array("success" => true, "message" => $url);
        } elseif (in_array($mimetype, $type_file)) { //FILE
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
            $url = $path . $fileName;
            move_uploaded_file($file['tmp_name'], $url);

            return array("success" => true, "message" => $url);
        } else {
            return array("success" => false, "message" => "El formato seleccionado es incorrecto");
        }
    }
}
