<?php
session_start();
require_once 'conexion.php';
require_once 'Cliente.php';

$cliente = new Cliente($conexion);


if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}

function limpiar($dato): string
{
    return htmlspecialchars((string)$dato, ENT_QUOTES, 'UTF-8');
}

$error = '';

// Analizo los datos al guardar o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    if (!hash_equals($_SESSION['token'], $_POST['token'] ?? '')) {
        $error = 'La solicitud no es válida. Actualiza la página e inténtalo otra vez.';
    } elseif ($accion === 'eliminar') {
        if ($id > 0) {
            $cliente->eliminar($id);
            header('Location: index.php?estado=eliminado');
            exit;
        }
        $error = 'El cliente seleccionado no es válido.';
    } elseif ($accion === 'crear' || $accion === 'actualizar') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($nombre === '' || $apellido === '' || $telefono === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'Completa todos los campos e ingresa un correo válido.';
        } elseif (strlen($nombre) > 100 || strlen($apellido) > 100 || strlen($correo) > 150 || strlen($telefono) > 20) {
            $error = 'Hay un dato demasiado largo. Revisa los campos.';
        } elseif ($accion === 'crear') {
            $cliente->crear($nombre, $apellido, $correo, $telefono);
            header('Location: index.php?estado=creado');
            exit;
        } elseif ($id > 0 && $cliente->obtener($id)) {
            $cliente->actualizar($id, $nombre, $apellido, $correo, $telefono);
            header('Location: index.php?estado=actualizado');
            exit;
        } else {
            $error = 'No se encontró el cliente que deseas actualizar.';
        }
    }
}

// Consultar un cliente
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
$idEliminar = filter_input(INPUT_GET, 'eliminar', FILTER_VALIDATE_INT);
$editar = $idEditar ? $cliente->obtener($idEditar) : false;
$eliminar = $idEliminar ? $cliente->obtener($idEliminar) : false;
$clientes = $cliente->listar();

$mensajes = [
    'creado' => 'Cliente registrado',
    'actualizado' => 'Cliente actualizado',
    'eliminado' => 'Cliente eliminado'
];
$estado = $_GET['estado'] ?? '';
$mensaje = $mensajes[$estado] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="contenedor">
        <h1>GESTIÓN DE CLIENTES</h1>

        <?php if ($mensaje): ?>
            <p class="aviso correcto"><?= limpiar($mensaje) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="aviso error"><?= limpiar($error) ?></p>
        <?php endif; ?>

        <section class="tarjeta">
            <h2><?= $editar ? 'Editar cliente' : 'Registrar cliente' ?></h2>
            <form method="post" action="index.php<?= $editar ? '?editar=' . (int)$editar['id'] : '' ?>">
                <input type="hidden" name="token" value="<?= limpiar($_SESSION['token']) ?>">
                <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
                <?php if ($editar): ?>
                    <input type="hidden" name="id" value="<?= (int)$editar['id'] ?>">
                <?php endif; ?>

                <div class="campos">
                    <label>Nombre
                        <input name="nombre" maxlength="100" required value="<?= limpiar($editar['nombre'] ?? '') ?>">
                    </label>
                    <label>Apellido
                        <input name="apellido" maxlength="100" required value="<?= limpiar($editar['apellido'] ?? '') ?>">
                    </label>
                    <label>Correo electrónico
                        <input type="email" name="correo" maxlength="150" required value="<?= limpiar($editar['correo'] ?? '') ?>">
                    </label>
                    <label>Teléfono
                        <input type="tel" name="telefono" maxlength="20" required value="<?= limpiar($editar['telefono'] ?? '') ?>">
                    </label>
                </div>
                <div class="acciones">
                    <button type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar cliente' ?></button>
                    <?php if ($editar): ?>
                        <a class="boton secundario" href="index.php">Cancelar edición</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($eliminar): ?>
            <section class="tarjeta">
                <h2>Confirmar eliminación</h2>
                <p>¿Deseas eliminar a <strong><?= limpiar($eliminar['nombre'] . ' ' . $eliminar['apellido']) ?></strong>?</p>
                <form method="post" action="index.php" class="acciones">
                    <input type="hidden" name="token" value="<?= limpiar($_SESSION['token']) ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int)$eliminar['id'] ?>">
                    <button type="submit" class="peligro">Eliminar</button>
                    <a class="boton secundario" href="index.php">Cancelar</a>
                </form>
            </section>
        <?php endif; ?>

        <section class="tarjeta">
            <h2>Clientes registrados (<?= count($clientes) ?>)</h2>
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $fila): ?>
                            <tr>
                                <td><?= (int)$fila['id'] ?></td>
                                <td><?= limpiar($fila['nombre']) ?></td>
                                <td><?= limpiar($fila['apellido']) ?></td>
                                <td><?= limpiar($fila['correo']) ?></td>
                                <td><?= limpiar($fila['telefono']) ?></td>
                                <td class="enlaces">
                                    <a href="index.php?editar=<?= (int)$fila['id'] ?>">Editar</a>
                                    <a class="eliminar" href="index.php?eliminar=<?= (int)$fila['id'] ?>">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$clientes): ?>
                            <tr><td colspan="6" class="vacio">No hay clientes registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
