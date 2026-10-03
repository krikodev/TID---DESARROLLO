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
        $file = $dataForm; // JSON con todos los datos del archivo seleccionado

        // 1. Verificar que no hubo error en la subida
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return array("success" => false, "message" => "Error al subir el archivo");
        }

        // 2. Verificar que realmente es un archivo subido vía HTTP POST
        if (!is_uploaded_file($file['tmp_name'])) {
            return array("success" => false, "message" => "Archivo inválido");
        }

        // 3. Límite de tamaño (ejemplo: 5MB, ajusta según necesites)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return array("success" => false, "message" => "El archivo excede el tamaño máximo permitido (5MB)");
        }

        // 4. Detectar el MIME type real leyendo el archivo (no confiar en $file['type'])
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // 5. Tipos permitidos (imágenes + documentos, unificados)
        $type_permitidos = array(
            "image/jpeg",
            "image/png",
            "application/pdf",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "text/plain",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
            "application/x-pkcs12",
        );

        if (!in_array($mimetype, $type_permitidos, true)) {
            return array("success" => false, "message" => "El formato seleccionado es incorrecto");
        }

        // 6. Crear carpeta si no existe, con permisos más restrictivos
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // 7. Generar nombre de archivo seguro (evita path traversal y sobrescritura)
        $extension = strtolower(pathinfo($name_file, PATHINFO_EXTENSION));
        $extensionesValidas = array('jpg', 'jpeg', 'png', 'pdf', 'pptx', 'txt', 'xlsx', 'xls', 'p12');

        if (!in_array($extension, $extensionesValidas, true)) {
            return array("success" => false, "message" => "Extensión de archivo no permitida");
        }

        $safeName = uniqid('file_', true) . '.' . $extension;
        $url = rtrim($path, '/') . '/' . $safeName;

        // 8. Mover el archivo y verificar que se guardó correctamente
        if (!move_uploaded_file($file['tmp_name'], $url)) {
            return array("success" => false, "message" => "No se pudo guardar el archivo");
        }

        return array("success" => true, "message" => $url);
    }
}
