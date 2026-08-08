<?php
require_once __DIR__ . '/classes/Biblioteca.php';

$biblioteca = new Biblioteca();
$mensaje = '';
$tipoMensaje = '';
$seccion = $_GET['action'] ?? 'libros';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'crear_libro':
                $libro = new Libro($_POST['titulo'] ?? '', $_POST['autor'] ?? '', $_POST['isbn'] ?? '', (int) ($_POST['cantidad'] ?? 1));
                $biblioteca->agregarLibro($libro);
                $mensaje = 'Libro creado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'editar_libro':
                $biblioteca->editarLibro((int) $_POST['id'], [
                    'titulo' => $_POST['titulo'] ?? '',
                    'autor' => $_POST['autor'] ?? '',
                    'isbn' => $_POST['isbn'] ?? '',
                    'cantidad' => (int) ($_POST['cantidad'] ?? 1)
                ]);
                $mensaje = 'Libro actualizado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'eliminar_libro':
                $biblioteca->eliminarLibro((int) $_POST['id']);
                $mensaje = 'Libro eliminado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'crear_usuario':
                $usuario = new Usuario($_POST['nombre'] ?? '', $_POST['email'] ?? '', $_POST['telefono'] ?? '');
                $biblioteca->agregarUsuario($usuario);
                $mensaje = 'Usuario creado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'editar_usuario':
                $biblioteca->editarUsuario((int) $_POST['id'], [
                    'nombre' => $_POST['nombre'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'telefono' => $_POST['telefono'] ?? ''
                ]);
                $mensaje = 'Usuario actualizado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'eliminar_usuario':
                $biblioteca->eliminarUsuario((int) $_POST['id']);
                $mensaje = 'Usuario eliminado correctamente.';
                $tipoMensaje = 'success';
                break;

            case 'prestar':
                $ok = $biblioteca->prestarLibro((int) $_POST['libro_id'], (int) $_POST['usuario_id']);
                $mensaje = $ok ? 'Préstamo registrado correctamente.' : 'No se pudo registrar el préstamo.';
                $tipoMensaje = $ok ? 'success' : 'error';
                break;

            case 'devolver':
                $ok = $biblioteca->devolverLibro((int) $_POST['prestamo_id']);
                $mensaje = $ok ? 'Devolución registrada correctamente.' : 'No se pudo registrar la devolución.';
                $tipoMensaje = $ok ? 'success' : 'error';
                break;
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = 'error';
    }
}

$libros = $biblioteca->obtenerLibros();
$usuarios = $biblioteca->obtenerUsuarios();
$prestamos = $biblioteca->obtenerPrestamosActivos();

$libroEditando = null;
if ($seccion === 'editar_libro' && isset($_GET['id'])) {
    $libroEditando = $biblioteca->buscarLibro((int) $_GET['id']);
}

$usuarioEditando = null;
if ($seccion === 'editar_usuario' && isset($_GET['id'])) {
    $usuarioEditando = $biblioteca->buscarUsuario((int) $_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Biblioteca</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f7fb; color: #233; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        nav { margin-bottom: 20px; background: #0f4c81; padding: 12px; border-radius: 8px; }
        nav a { margin-right: 15px; text-decoration: none; color: white; font-weight: bold; }
        h1, h2 { color: #0f4c81; }
        .panel { margin-bottom: 24px; padding: 16px; border: 1px solid #dfe7f2; border-radius: 10px; background: #fbfdff; }
        form { display: grid; gap: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        input, select, button { padding: 8px; border-radius: 6px; border: 1px solid #c6d5e2; }
        button { background: #0f4c81; color: white; cursor: pointer; }
        .btn-secondary { background: #6c757d; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; border-bottom: 1px solid #e5eaf0; text-align: left; }
        .mensaje { padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .mensaje.success { background: #e8f8ef; color: #1f7a45; }
        .mensaje.error { background: #fdecec; color: #b11e1e; }
        .actions a, .actions form { display: inline-block; margin-right: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistema de Gestión de Biblioteca</h1>

        <nav>
            <a href="index.php?action=libros">Libros</a>
            <a href="index.php?action=usuarios">Usuarios</a>
            <a href="index.php?action=prestamos">Préstamos</a>
        </nav>

        <?php if ($mensaje !== ''): ?>
            <div class="mensaje <?= htmlspecialchars($tipoMensaje) ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($seccion === 'libros' || $seccion === 'editar_libro'): ?>
            <div class="panel">
                <h2><?= $seccion === 'editar_libro' ? 'Editar libro' : 'Agregar libro' ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?= $seccion === 'editar_libro' ? 'editar_libro' : 'crear_libro' ?>">
                    <?php if ($seccion === 'editar_libro' && $libroEditando): ?>
                        <input type="hidden" name="id" value="<?= (int) $libroEditando['id'] ?>">
                    <?php endif; ?>
                    <div class="grid">
                        <input type="text" name="titulo" placeholder="Título" value="<?= $seccion === 'editar_libro' && $libroEditando ? htmlspecialchars($libroEditando['titulo']) : '' ?>" required>
                        <input type="text" name="autor" placeholder="Autor" value="<?= $seccion === 'editar_libro' && $libroEditando ? htmlspecialchars($libroEditando['autor']) : '' ?>" required>
                        <input type="text" name="isbn" placeholder="ISBN" value="<?= $seccion === 'editar_libro' && $libroEditando ? htmlspecialchars($libroEditando['isbn']) : '' ?>">
                        <input type="number" name="cantidad" min="0" value="<?= $seccion === 'editar_libro' && $libroEditando ? (int) $libroEditando['cantidad'] : 1 ?>" required>
                    </div>
                    <div class="actions">
                        <button type="submit">Guardar</button>
                        <?php if ($seccion === 'editar_libro'): ?>
                            <a href="index.php?action=libros"><button type="button" class="btn-secondary">Cancelar</button></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h2>Listado de libros</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>ISBN</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($libros as $libro): ?>
                            <tr>
                                <td><?= (int) $libro['id'] ?></td>
                                <td><?= htmlspecialchars($libro['titulo']) ?></td>
                                <td><?= htmlspecialchars($libro['autor']) ?></td>
                                <td><?= htmlspecialchars($libro['isbn']) ?></td>
                                <td><?= (int) $libro['cantidad'] ?></td>
                                <td class="actions">
                                    <a href="index.php?action=editar_libro&id=<?= (int) $libro['id'] ?>">Editar</a>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="eliminar_libro">
                                        <input type="hidden" name="id" value="<?= (int) $libro['id'] ?>">
                                        <button type="submit" class="btn-secondary">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($seccion === 'usuarios' || $seccion === 'editar_usuario'): ?>
            <div class="panel">
                <h2><?= $seccion === 'editar_usuario' ? 'Editar usuario' : 'Agregar usuario' ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?= $seccion === 'editar_usuario' ? 'editar_usuario' : 'crear_usuario' ?>">
                    <?php if ($seccion === 'editar_usuario' && $usuarioEditando): ?>
                        <input type="hidden" name="id" value="<?= (int) $usuarioEditando['id'] ?>">
                    <?php endif; ?>
                    <div class="grid">
                        <input type="text" name="nombre" placeholder="Nombre" value="<?= $seccion === 'editar_usuario' && $usuarioEditando ? htmlspecialchars($usuarioEditando['nombre']) : '' ?>" required>
                        <input type="email" name="email" placeholder="Email" value="<?= $seccion === 'editar_usuario' && $usuarioEditando ? htmlspecialchars($usuarioEditando['email']) : '' ?>" required>
                        <input type="text" name="telefono" placeholder="Teléfono" value="<?= $seccion === 'editar_usuario' && $usuarioEditando ? htmlspecialchars($usuarioEditando['telefono']) : '' ?>">
                    </div>
                    <div class="actions">
                        <button type="submit">Guardar</button>
                        <?php if ($seccion === 'editar_usuario'): ?>
                            <a href="index.php?action=usuarios"><button type="button" class="btn-secondary">Cancelar</button></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h2>Listado de usuarios</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= (int) $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                                <td class="actions">
                                    <a href="index.php?action=editar_usuario&id=<?= (int) $usuario['id'] ?>">Editar</a>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="eliminar_usuario">
                                        <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                        <button type="submit" class="btn-secondary">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($seccion === 'prestamos'): ?>
            <div class="panel">
                <h2>Registrar préstamo</h2>
                <form method="post">
                    <input type="hidden" name="action" value="prestar">
                    <div class="grid">
                        <select name="libro_id" required>
                            <option value="">Selecciona un libro</option>
                            <?php foreach ($libros as $libro): ?>
                                <option value="<?= (int) $libro['id'] ?>"><?= htmlspecialchars($libro['titulo']) ?> (<?= (int) $libro['cantidad'] ?> disponibles)</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="usuario_id" required>
                            <option value="">Selecciona un usuario</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= (int) $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="actions">
                        <button type="submit">Registrar préstamo</button>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h2>Préstamos activos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Libro</th>
                            <th>Usuario</th>
                            <th>Fecha préstamo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prestamos as $prestamo): ?>
                            <tr>
                                <td><?= (int) $prestamo['id'] ?></td>
                                <td><?= htmlspecialchars($prestamo['titulo']) ?></td>
                                <td><?= htmlspecialchars($prestamo['nombre']) ?></td>
                                <td><?= htmlspecialchars($prestamo['fecha_prestamo']) ?></td>
                                <td class="actions">
                                    <form method="post">
                                        <input type="hidden" name="action" value="devolver">
                                        <input type="hidden" name="prestamo_id" value="<?= (int) $prestamo['id'] ?>">
                                        <button type="submit">Registrar devolución</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
