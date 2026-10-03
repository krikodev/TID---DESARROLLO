class Upload {
    static suportedImages = ["image/png", "image/jpeg", "image/jpg"];
    static suportedFile = {
        'pdf': ["application/pdf"],
        'pfx': ["application/x-pkcs12"]
    };
    constructor() {
    }

    static uploadImage(inputFile, targetImage) {
        inputFile.addEventListener('change', () => {
            if (Upload.validate("img", inputFile, null)) {
                const reader = new FileReader();
                reader.addEventListener("load", () => {
                    const uploaded_image = reader.result;
                    targetImage.style.backgroundImage = `url(${uploaded_image})`;
                });
                reader.readAsDataURL(inputFile.files[0]);
            } else {
                inputFile.value = "";
                targetImage.removeAttribute("style");
                $.toast({
                    heading: 'Error',
                    text: "Formato incorrecto",
                    showHideTransition: 'plain',
                    icon: 'error',
                    position: 'botoom-left',
                    loader: true,
                    loaderBg: '#9EC600'
                })
                targetImage.style.backgroundImage = `url('public/image/upload/notupload_img.png')`;
            }

        })
    }

    static uploadFile(inputFile, targetImage, type_file) {
        inputFile.addEventListener('change', () => {
            if (Upload.validate("file", inputFile, type_file)) {
                targetImage.style.backgroundImage = `url('public/image/upload/upload_file${type_file}.png')`;
            } else {
                inputFile.value = "";
                targetImage.removeAttribute("style");
                $.toast({
                    heading: 'Error',
                    text: "Formato incorrecto",
                    showHideTransition: 'plain',
                    icon: 'error',
                    position: 'botoom-left',
                    loader: true,
                    loaderBg: '#9EC600'
                })
                targetImage.style.backgroundImage = `url('public/image/upload/notupload_file${type_file}.png')`;
            }
        })
    }

    static validate(type_validate, inputFile, type_file = null) {
        try {
            let typeFile = inputFile.files[0].type;
            let validate;
            switch (type_validate) {
                case "img":
                    validate = Upload.suportedImages.includes(typeFile);
                    break;
                case "file":
                    validate = Upload.suportedFile[type_file].includes(typeFile);
                    break;
                default:
                    break;
            }
            if (validate) {
                return true;
            } else {
                return false;
            }
        } catch (Exception) {
            return false;
        }
    }
}

export { Upload };