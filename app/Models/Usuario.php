<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\RolUsuario;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false; // Porque tu tabla en Python no usa created_at/updated_at estándar

    protected $fillable = [
        'nombre', 
        'pin_hash', 
        'password_hash', 
        'rol', 
        'activo', 
        'foto_url'
    ];

    protected $hidden = [
        'pin_hash',
        'password_hash',
    ];

    protected $casts = [
        'rol' => RolUsuario::class,
        'activo' => 'boolean',
    ];

    // Relaciones
    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'id_usuario_creacion', 'id_usuario');
    }

    public function arqueos()
    {
        return $this->hasMany(ArqueoCaja::class, 'id_usuario', 'id_usuario');
    }

    // Verificación de PIN y contraseña (formato Python: sal$hash, PBKDF2-SHA256, 100000 iteraciones)
    public function verificarPin(string $pin): bool
    {
        return self::verificarHash($pin, $this->pin_hash);
    }

    public function verificarPassword(string $password): bool
    {
        return $this->password_hash !== null
            && self::verificarHash($password, $this->password_hash);
    }

    public static function generarHash(string $valor): string
    {
        $sal = bin2hex(random_bytes(16));
        $hash = hash_pbkdf2('sha256', $valor, $sal, 100000, 0, false);
        return $sal . '$' . $hash;
    }

    private static function verificarHash(string $valor, string $almacenado): bool
    {
        if (!str_contains($almacenado, '$')) {
            return false;
        }

        [$sal, $hash] = explode('$', $almacenado, 2);
        $esperado = hash_pbkdf2('sha256', $valor, $sal, 100000, 0, false);

        return hash_equals($hash, $esperado);
    }
}