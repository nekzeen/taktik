<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'consent_at',
        'last_activity_at',
        'can_create_tournaments',
        'can_create_matches',
        'max_open_tournaments',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'consent_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'can_create_tournaments' => 'boolean',
            'can_create_matches' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone'])
            ->logOnlyDirty();
    }

    // Relations
    public function tournaments()
    {
        return $this->hasMany(Tournament::class, 'created_by');
    }

    public function armyLists()
    {
        return $this->hasMany(ArmyList::class);
    }

    public function matchesAsPlayer1()
    {
        return $this->hasMany(GameMatch::class, 'player1_id');
    }

    public function matchesAsPlayer2()
    {
        return $this->hasMany(GameMatch::class, 'player2_id');
    }

    public function calendarSlots()
    {
        return $this->hasMany(CalendarSlot::class);
    }

    public function matchRequests()
    {
        return $this->hasMany(MatchRequest::class, 'creator_id');
    }

    public function playerMatchRequests()
    {
        return $this->hasMany(PlayerMatchRequest::class, 'requester_id');
    }

    /**
     * Compter le nombre de tournois ouverts créés par cet utilisateur
     */
    public function countOpenTournaments(): int
    {
        return $this->tournaments()
            ->where('status', 'open')
            ->count();
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'moderator']);
    }
}
