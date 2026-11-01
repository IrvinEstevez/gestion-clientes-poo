<?php
// Agrupa el CRUD sobre la tabla clientes
class Cliente
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function crear(string $nombre, string $apellido, string $correo, string $telefono): void
    {
        $sql = 'INSERT INTO clientes (nombre, apellido, correo, telefono) VALUES (?, ?, ?, ?)';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([$nombre, $apellido, $correo, $telefono]);
    }

    public function listar(): array
    {
        return $this->conexion->query('SELECT * FROM clientes ORDER BY id DESC')->fetchAll();
    }

    public function obtener(int $id): array|false
    {
        $consulta = $this->conexion->prepare('SELECT * FROM clientes WHERE id = ?');
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function actualizar(int $id, string $nombre, string $apellido, string $correo, string $telefono): void
    {
        $sql = 'UPDATE clientes SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id = ?';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([$nombre, $apellido, $correo, $telefono, $id]);
    }

    public function eliminar(int $id): void
    {
        $consulta = $this->conexion->prepare('DELETE FROM clientes WHERE id = ?');
        $consulta->execute([$id]);
    }
}
