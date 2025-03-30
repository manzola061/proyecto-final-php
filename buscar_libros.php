<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$termino_busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

include("includes/cabecera.php");
?>

<div class="container p-4">
    <h3 class="mb-4">Resultados de búsqueda: "<?php echo htmlspecialchars($termino_busqueda) ?>"</h3>

    <?php if (!empty($termino_busqueda)): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categorías</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id_usuario = $_SESSION['id_usuario'];
                    $query = "SELECT l.id, l.titulo, l.descripcion, 
                             CONCAT(a.nombre, ' ', a.apellido) AS autor,
                             GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
                             FROM libro l 
                             JOIN autor a ON l.id_autor = a.id
                             LEFT JOIN libro_categoria lc ON l.id = lc.id_libro
                             LEFT JOIN categoria c ON lc.id_categoria = c.id
                             WHERE l.id_usuario = ? AND l.titulo LIKE ?
                             GROUP BY l.id";

                    $stmt = $conn->prepare($query);
                    $termino = "%$termino_busqueda%";
                    $stmt->bind_param("is", $id_usuario, $termino);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    
                    if ($resultado->num_rows > 0) {
                        while($fila = $resultado->fetch_assoc()) {
                            echo '<tr>
                                <td>'.htmlspecialchars($fila['titulo']).'</td>
                                <td>'.htmlspecialchars($fila['autor']).'</td>
                                <td>'.htmlspecialchars($fila['categorias'] ?? 'Sin categorías').'</td>
                                <td>'.htmlspecialchars($fila['descripcion']).'</td>
                            </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" class="text-center">No se encontraron libros que coincidan con la búsqueda</td></tr>';
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Ingrese un término de búsqueda</div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-primary mt-3">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado completo
    </a>
</div>

<?php include("includes/pie_pagina.php") ?>