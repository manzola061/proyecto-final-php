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

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
    exit();
}

$query = "SELECT id FROM categoria WHERE nombre = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'La categoría ya existe']);
    exit();
}

$query = "INSERT INTO categoria (nombre, descripcion) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $nombre, $descripcion);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'id' => $stmt->insert_id,
        'nombre' => $nombre
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear categoría: ' . $stmt->error]);
}

$stmt->close();
?>