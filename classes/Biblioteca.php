<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Libro.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Prestamo.php';

class Biblioteca {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function agregarLibro(Libro $libro) {
        $stmt = $this->conn->prepare('INSERT INTO libros (titulo, autor, isbn, cantidad) VALUES (?, ?, ?, ?)');
        return $stmt->execute([
            $libro->getTitulo(),
            $libro->getAutor(),
            $libro->getIsbn(),
            (int) $libro->getCantidad()
        ]);
    }

    public function editarLibro($id, $nuevosDatos) {
        $campos = [];
        $valores = [];

        if (isset($nuevosDatos['titulo']) && $nuevosDatos['titulo'] !== '') {
            $campos[] = 'titulo = ?';
            $valores[] = trim($nuevosDatos['titulo']);
        }

        if (isset($nuevosDatos['autor']) && $nuevosDatos['autor'] !== '') {
            $campos[] = 'autor = ?';
            $valores[] = trim($nuevosDatos['autor']);
        }

        if (isset($nuevosDatos['isbn']) && $nuevosDatos['isbn'] !== '') {
            $campos[] = 'isbn = ?';
            $valores[] = trim($nuevosDatos['isbn']);
        }

        if (isset($nuevosDatos['cantidad'])) {
            $campos[] = 'cantidad = ?';
            $valores[] = (int) $nuevosDatos['cantidad'];
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = (int) $id;
        $stmt = $this->conn->prepare('UPDATE libros SET ' . implode(', ', $campos) . ' WHERE id = ?');
        return $stmt->execute($valores);
    }

    public function eliminarLibro($id) {
        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare('DELETE FROM prestamos WHERE libro_id = ?');
            $stmt->execute([(int) $id]);

            $stmt = $this->conn->prepare('DELETE FROM libros WHERE id = ?');
            $stmt->execute([(int) $id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function obtenerLibros() {
        $stmt = $this->conn->query('SELECT * FROM libros ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function buscarLibro($id) {
        $stmt = $this->conn->prepare('SELECT * FROM libros WHERE id = ?');
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function agregarUsuario(Usuario $usuario) {
        $stmt = $this->conn->prepare('INSERT INTO usuarios (nombre, email, telefono) VALUES (?, ?, ?)');
        return $stmt->execute([
            $usuario->getNombre(),
            $usuario->getEmail(),
            $usuario->getTelefono()
        ]);
    }

    public function editarUsuario($id, $nuevosDatos) {
        $campos = [];
        $valores = [];

        if (isset($nuevosDatos['nombre']) && $nuevosDatos['nombre'] !== '') {
            $campos[] = 'nombre = ?';
            $valores[] = trim($nuevosDatos['nombre']);
        }

        if (isset($nuevosDatos['email']) && $nuevosDatos['email'] !== '') {
            $campos[] = 'email = ?';
            $valores[] = trim($nuevosDatos['email']);
        }

        if (isset($nuevosDatos['telefono'])) {
            $campos[] = 'telefono = ?';
            $valores[] = trim($nuevosDatos['telefono']);
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = (int) $id;
        $stmt = $this->conn->prepare('UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = ?');
        return $stmt->execute($valores);
    }

    public function eliminarUsuario($id) {
        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare('DELETE FROM prestamos WHERE usuario_id = ?');
            $stmt->execute([(int) $id]);

            $stmt = $this->conn->prepare('DELETE FROM usuarios WHERE id = ?');
            $stmt->execute([(int) $id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function obtenerUsuarios() {
        $stmt = $this->conn->query('SELECT * FROM usuarios ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function buscarUsuario($id) {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function prestarLibro($libro_id, $usuario_id) {
        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare('SELECT cantidad FROM libros WHERE id = ? LIMIT 1');
            $stmt->execute([(int) $libro_id]);
            $libro = $stmt->fetch();

            if (!$libro || (int) $libro['cantidad'] <= 0) {
                throw new Exception('No hay stock disponible');
            }

            $stmt = $this->conn->prepare('INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, estado) VALUES (?, ?, ?, "activo")');
            $stmt->execute([(int) $libro_id, (int) $usuario_id, date('Y-m-d')]);

            $stmt = $this->conn->prepare('UPDATE libros SET cantidad = cantidad - 1 WHERE id = ?');
            $stmt->execute([(int) $libro_id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function devolverLibro($prestamo_id) {
        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare('SELECT libro_id FROM prestamos WHERE id = ? AND estado = "activo" LIMIT 1');
            $stmt->execute([(int) $prestamo_id]);
            $prestamo = $stmt->fetch();

            if (!$prestamo) {
                throw new Exception('Préstamo no encontrado o ya devuelto');
            }

            $stmt = $this->conn->prepare('UPDATE prestamos SET fecha_devolucion = ?, estado = "devuelto" WHERE id = ?');
            $stmt->execute([date('Y-m-d'), (int) $prestamo_id]);

            $stmt = $this->conn->prepare('UPDATE libros SET cantidad = cantidad + 1 WHERE id = ?');
            $stmt->execute([(int) $prestamo['libro_id']]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function obtenerPrestamosActivos() {
        $stmt = $this->conn->query('SELECT p.id, p.libro_id, p.usuario_id, p.fecha_prestamo, p.fecha_devolucion, p.estado, l.titulo, u.nombre FROM prestamos p JOIN libros l ON l.id = p.libro_id JOIN usuarios u ON u.id = p.usuario_id WHERE p.estado = "activo" ORDER BY p.id DESC');
        return $stmt->fetchAll();
    }
}
