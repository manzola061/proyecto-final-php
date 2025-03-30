<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$librosPorPagina = 5;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $librosPorPagina;

$id_usuario = $_SESSION['id_usuario'];
$queryTotal = "SELECT COUNT(*) as total FROM libro WHERE id_usuario = '$id_usuario'";
$resultadoTotal = mysqli_query($conn, $queryTotal);
$totalLibros = mysqli_fetch_assoc($resultadoTotal)['total'];
$totalPaginas = ceil($totalLibros / $librosPorPagina);

$query = "SELECT l.id, l.titulo, l.descripcion, 
         CONCAT(a.nombre, ' ', a.apellido) AS autor,
         GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
         FROM libro l 
         JOIN autor a ON l.id_autor = a.id
         LEFT JOIN libro_categoria lc ON l.id = lc.id_libro
         LEFT JOIN categoria c ON lc.id_categoria = c.id
         WHERE l.id_usuario = '$id_usuario'
         GROUP BY l.id
         LIMIT $librosPorPagina OFFSET $offset";
$resultado_libros = mysqli_query($conn, $query);
?>

<?php include("includes/cabecera.php") ?>

<div class="container p-4">
    <div class="row">
        <div class="col-md-4">
            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            } ?>

            <div class="card card-body">
                <form action="crear_libro.php" method="POST">
                    <div class="form-group mb-3">
                        <input type="text" name="titulo" class="form-control" placeholder="Título del libro" required autofocus>
                    </div>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <select name="id_autor" class="form-control" required id="selectAutor">
                                <option value="">Seleccione un autor</option>
                                <?php
                                $query_autores = "SELECT id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM autor ORDER BY nombre, apellido";
                                $resultado_autores = mysqli_query($conn, $query_autores);

                                while ($autor = mysqli_fetch_array($resultado_autores)) {
                                    echo '<option value="' . $autor['id'] . '">' . $autor['nombre_completo'] . '</option>';
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
                                    echo '<option value="' . $categoria['id'] . '">' . $categoria['nombre'] . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                                <i class="fa-solid fa-plus"></i> Nueva
                            </button>
                        </div>
                        <input type="hidden" name="categorias" id="categoriasSeleccionadas">
                    </div>
                    <div class="form-group mb-3">
                        <textarea name="descripcion" rows="4" class="form-control" placeholder="Descripción (opcional)"></textarea>
                    </div>
                    <input type="submit" class="btn btn-success btn-block" name="crear_libro" value="Agregar libro">
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categorías</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($fila = mysqli_fetch_array($resultado_libros)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['titulo']) ?></td>
                            <td><?php echo htmlspecialchars($fila['autor']) ?></td>
                            <td><?php echo htmlspecialchars($fila['categorias'] ?? 'Sin categorías') ?></td>
                            <td><?php echo !empty($fila['descripcion']) ? htmlspecialchars($fila['descripcion']) : 'Sin descripción' ?></td>
                            <td style="white-space: nowrap;">
                                <a href="editar_libro.php?id=<?php echo $fila['id'] ?>" class="btn btn-primary">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="eliminar_libro.php?id=<?php echo $fila['id'] ?>" class="btn btn-danger">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($paginaActual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
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

            alert('Autor creado exitosamente');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear el autor');
    });
});

document.getElementById('selectCategorias').addEventListener('change', function() {
    const selected = Array.from(this.selectedOptions).map(opt => opt.value);
    document.getElementById('categoriasSeleccionadas').value = selected.join(',');
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

            alert('Categoría creada exitosamente');
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