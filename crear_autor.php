<?php
include("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!isset($_POST['nombre'], $_POST['apellido'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$correo = trim($_POST['correo'] ?? '');

if (empty($nombre) || empty($apellido)) {
    echo json_encode(['success' => false, 'message' => 'Nombre y apellido son obligatorios']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO autor (nombre, apellido, correo) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $apellido, $correo);

if ($stmt->execute()) {
    $id_autor = $stmt->insert_id;
    
    echo json_encode([
        'success' => true,
        'id' => $id_autor,
        'nombre_completo' => $nombre . ' ' . $apellido
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear autor: ' . $stmt->error]);
}

$stmt->close();
?>