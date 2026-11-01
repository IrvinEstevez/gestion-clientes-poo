<?php
// Conexión a la base de datos
$servidor = '127.0.0.1';
$baseDatos = 'gestion_clientes';
$usuario = 'root';
$contrasena = '';

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    exit('No se pudo conectar a la base de datos. Revisa MySQL, el nombre de la base y los datos de conexion.php.');
}
