<?php

namespace App\Models;
use Illuminate\Http\Request;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'driver_license_number',
        'role'
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }
    public function clients()
    {
        return $this->hasMany(Client::class);
    } 
    public function drivenVehicle()
    {
    return $this->hasOne(Vehicle::class, 'driver_id');
    }
    public function assignedVehicleId(): ?int 
    { 
        return $this->role === 'chauffeur' ? $this->drivenVehicle?->id : $this->vehicle_id;
    }
    
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['super_admin', 'responsable']);
    }
    public function affecterVehicule(?int $vehicleId): bool
    {
        return DB::transaction(function () use ($vehicleId) {
            if ($vehicleId) {
                $vehicle = Vehicle::whereKey($vehicleId)
                    ->where('company_id', $this->company_id)
                    ->lockForUpdate()
                    ->first();

                if (! $vehicle || ($vehicle->driver_id && (int) $vehicle->driver_id !== (int) $this->id)) {
                    return false;
                }

                $vehicle->update(['driver_id' => $this->id]);
            }

            Vehicle::where('driver_id', $this->id)
                ->when($vehicleId, fn ($query) => $query->whereKeyNot($vehicleId))
                ->update(['driver_id' => null]);

            return true;
        });
    }

}
