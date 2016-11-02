<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');



if (isset($_FILES['file'], $_POST['id'])) {
    $ext = explode('.', $_FILES['file']['name'])[1] ?? 'tmp';
    move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/uploads/' . $_POST['id'] . '.' . $ext);
}

echo json_encode([
    'data' => [
        'success' => true,
        'file' => $ext
    ]
]);