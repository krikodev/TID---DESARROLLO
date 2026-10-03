<?php

class Validation
{

    public function __construct()
    {
    }

    public function validateData($data)
    {
        foreach ($data as $key => $value) {
            $text = trim($value);
            if (strlen($text) == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function validateTodoList($data)
    {
        $validateError = 0;
        for ($i = 0; $i < count($data); $i++) {
            for ($x = 0; $x < count($data[$i]); $x++) {
                $element = trim($data[$i][$x]);
                if (strlen($element) == 0) {
                    $validateError += 1;
                }
            }
        }
        if ($validateError > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function validateString($data)
    {
        $x = 0;
        for ($i = 0; $i < count($data); $i++) {

            if (preg_match_all("/^([a-zA-Z áéíóúáéíóúÁÉÍÓÚñÑ]+)$/", $data[$i])) {
                // if (preg_match_all("/^([a-zA-Z ]+)$/", $data[$i])) {
                continue;
            } else {
                $x++;
            }
        }
        if ($x > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function validateEmail($email)
    {
        $regex = "/^([a-zA-Z0-9\.]+@+[a-zA-Z]+(\.)+[a-zA-Z]{2,3}+(\.)+[a-zA-Z]{2,3})$/";
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateNum($data)
    {
        $x = 0;
        for ($i = 0; $i < count($data); $i++) {
            if (is_numeric($data[$i])) {
                continue;
            } else {
                $x++;
            }
        }
        if ($x > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function validateMoney($num)
    {
        if (is_numeric($num)) {
            return true;
        } else {
            return false;
        }
    }
}
