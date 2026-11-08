<?php

function getInput(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_array($data)) {
        return $data;
    }
    return $_POST;
}