<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'plan' => $this->plan,
            'signed_in' => $this->signed_in,
            'backup_on' => $this->backup_on,
            'last_backup_at' => $this->last_backup_at,
            'created_at' => $this->created_at,
        ];
    }
}
