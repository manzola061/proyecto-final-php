<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['message'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id_usuario = $_SESSION['id_usuario'];

    $query = "SELECT l.*, 
              CONCAT(a.nombre, ' ', a.apellido) AS nombre_autor,
              GROUP_CONCAT(c.id) AS ids_categorias
              FROM libro l
              JOIN autor a ON l.id_autor = a.id
              LEFT JOIN libro_categoria lc ON l.id = lc.id_libro
              LEFT JOIN categoria c ON lc.id_categoria = c.id
              WHERE l.id = ? AND l.id_usuario = ?
              GROUP BY l.id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id, $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $titulo = $fila['titulo'];
        $id_autor = $fila['id_autor'];
        $nombre_autor = $fila['nombre_autor'];
        $descripcion = $fila['descripcion'];
        $ids_categorias = $fila['ids_categorias'] ? explode(',', $fila['ids_categorias']) : [];
    } else {
        $_SESSION['message'] = "Libro no encontrado";
        $_SESSION['message_type'] = "danger";
        header("Location: index.php");
        exit();
    }
}

if (isset($_POST['editar'])) {
    $id = $_GET['id'];
    $titulo = trim($_POST['titulo']);
    $id_autor = intval($_POST['id_autor']);
    $descripcion = trim($_POST['descripcion']);
    $id_usuario = $_SESSION['id_usuario'];
    $categorias = isset($_POST['categorias']) ? array_filter(explode(',', $_POST['categorias']), 'is_numeric') : [];

    if (empty($titulo) || empty($id_autor)) {
        $_SESSION['message'] = 'Título y autor son obligatorios';
        $_SESSION['message_type'] = 'danger';
        header("Location: editar_libro.php?id=$id");
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        $query = "UPDATE libro SET titulo = ?, id_autor = ?, descripcion = ? 
                 WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisii", $titulo, $id_autor, $descripcion, $id, $id_usuario);
        $stmt->execute();

        $query_delete = "DELETE FROM libro_categoria WHERE id_libro = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();

        if (!empty($categorias)) {
            $query_insert = "INSERT INTO libro_categoria (id_libro, id_categoria) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($query_insert);

            foreach ($categorias as $id_categoria) {
                $stmt_insert->bind_param("ii", $id, $id_categoria);
                $stmt_insert->execute();
            }
        }

        mysqli_commit($conn);
        $_SESSION['message'] = "Libro editado correctamente";
        $_SESSION['message_type'] = "primary";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    header("Location: index.php");
    exit();
}

include("includes/cabecera.php");
?>

<div class="container p-4">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card card-body">
                <h3 class="text-center mb-4">Editar libro</h3>
                <form action="editar_libro.php?id=<?php echo $id; ?>" method="POST">
                    <div class="form-group mb-3">
                        <label>Título:</label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" class="form-control" placeholder="Título del libro" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Autor:</label>
                        <div class="input-group">
                            <select name="id_autor" class="form-control" required id="selectAutor">
                                <option value="">Seleccione un autor</option>
                                <?php
                                $query_autores = "SELECT id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM autor ORDER BY nombre, apellido";
                                $resultado_autores = mysqli_query($conn, $query_autores);

                                while ($autor = mysqli_fetch_array($resultado_autores)) {
                                    $selected = ($autor['id'] == $id_autor) ? 'selected' : '';
                                    echo '<option value="' . $autor['id'] . '" ' . $selected . '>' . htmlspecialchars($autor['nombre_completo']) . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrearAutor">
                                <i class="fa-solid fa-plus"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Categorías:</label>
                        <div class="input-group">
                            <select class="form-control" id="selectCategorias" multiple>
                                <?php
                                $query_categorias = "SELECT id, nombre FROM categoria ORDER BY nombre";
                                $resultado_categorias = mysqli_query($conn, $query_categorias);

                                while ($categoria = mysqli_fetch_array($resultado_categorias)) {
                                    $selected = in_array($categoria['id'], $ids_categorias) ? 'selected' : '';
                                    echo '<option value="' . $categoria['id'] . '" ' . $selected . '>' . htmlspecialchars($categoria['nombre']) . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                                <i class="fa-solid fa-plus"></i> Nueva
                            </button>
                        </div>
                        <input type="hidden" name="categorias" id="categoriasSeleccionadas" value="<?php echo implode(',', $ids_categorias); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label>Descripción:</label>
                        <textarea name="descripcion" rows="4" class="form-control" placeholder="Descripción (opcional)"><?php echo htmlspecialchars($descripcion); ?></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" name="editar">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearAutor" tabindex="-1" aria-labelledby="modalCrearAutorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearAutorLabel">Agregar nuevo autor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearAutor">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" name="apellido" class="form-control" placeholder="Apellido" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="email" name="correo" class="form-control" placeholder="Correo electrónico">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar autor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-labelledby="modalCrearCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearCategoriaLabel">Agregar nueva categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearCategoria">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de la categoría" required>
                    </div>
                    <div class="form-group mb-3">
                        <textarea name="descripcion" class="form-control" placeholder="Descripción (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('selectCategorias').addEventListener('change', function() {
    const selected = Array.from(this.selectedOptions).map(opt => opt.value);
    document.getElementById('categoriasSeleccionadas').value = selected.join(',');
});

document.getElementById('formCrearAutor').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('crear_autor.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('selectAutor');
            const newOption = new Option(data.nombre_completo, data.id, true, true);
            select.add(newOption);

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearAutor'));
            modal.hide();
            this.reset();

        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear el autor');
    });
});

document.getElementById('formCrearCategoria').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('crear_categoria.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('selectCategorias');
            const newOption = new Option(data.nombre, data.id, true, true);
            select.add(newOption);

            const selected = Array.from(select.selectedOptions).map(opt => opt.value);
            document.getElementById('categoriasSeleccionadas').value = selected.join(',');

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearCategoria'));
            modal.hide();
            this.reset();

        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear la categoría');
    });
});
</script>

<?php include("includes/pie_pagina.php") ?>