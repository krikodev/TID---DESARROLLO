
export class Validate {

    constructor() {

    }

    static validateData(data) {
        let validateError = 0;
        for (let index = 0; index < data.length; index++) {
            if (data[index]) {
                if ((data[index].trim()).length == 0) {
                    validateError += 1;
                }
            } else {
                validateError += 1;
            }
        }
        if (validateError > 0) {
            return false;
        } else {
            return true;
        }
    }

    static validateTodoList(data) {
        let validateError = 0;
        for (let i = 0; i < data.length; i++) {
            for (let x = 0; x < data[i].length; x++) {
                let element = data[i][x].trim();
                if (element.length == 0) {
                    validateError += 1;
                }
            }
        }
        if (validateError > 0) {
            return false;
        } else {
            return true;
        }
    }

    static allowInputNum(inputData = []) {
        inputData.forEach(element => {
            $(element).bind('keypress', function (event) {
                var regex = new RegExp("^[0-9]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    }

    static allowInputStringSpace(inputData = []) {
        inputData.forEach(element => {
            $(element).bind('keypress', function (event) {
                var regex = new RegExp("^[a-zA-Z- ]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    }

    static allowInputAlphaNum(inputData = []) {
        inputData.forEach(element => {
            $(element).bind('keypress', function (event) {
                var regex = new RegExp("^[a-zA-Z0-9- ]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    }

    static allowInputMoney(inputData = []) {
        inputData.forEach(element => {
            $(element).bind('keypress', function (event) {
                var regex = new RegExp("^[0-9.]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    }
}