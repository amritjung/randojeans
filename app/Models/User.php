<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
// phone: needed for the customer profile/checkout phone field.
// is_admin: needed so the Admin resource's toggle can actually save.
// It's safe to include here because every place in this app that
// creates/updates a User passes an explicit array of fields (never
// raw, unfiltered request input) — so even though is_admin is
// mass-assignable, no public-facing form actually includes that key.
#[Fillable(['name', 'email', 'password', 'phone', 'is_admin', 'address_street', 'address_suburb', 'address_state', 'address_postcode'])]
#[Hidden(['password', 'remember_token'])]

// MustVerifyEmail is a Laravel interface — implementing it enables all
// the built-in email verification behavior (the notification email,
// the verified/unverified checks, the 'verified' middleware) without
// us having to write that logic ourselves.
class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

     use SoftDeletes;
        // ... rest of the existing class stays exactly as-is (HasFactory,
        // Notifiable, or whatever other traits are already there)
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // This is the actual security gate for the whole /admin panel. Filament
        // calls this on every login attempt — if it returns false, the person
        // is rejected from the panel entirely, even with a correct password.
        // Without this method, Filament defaults to allowing ANY authenticated
        // user in, which would let customers into the admin panel.
        public function canAccessPanel(Panel $panel): bool
        {
            return $this->is_admin;
        }
    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    // Combines the structured fields into one display line, e.g.
// "12 Smith St, Bondi NSW 2026" — used wherever we need a single
// human-readable address string rather than the separate fields
// (like showing it in the order admin or an email).
    public function fullAddress(): ?string
        {
            if (! $this->address_street) {
                return null;
            }

            return "{$this->address_street}, {$this->address_suburb} {$this->address_state} {$this->address_postcode}";
        }
}
