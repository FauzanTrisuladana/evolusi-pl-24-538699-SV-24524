<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class LinkResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_link,
            'name' => $this->name,
            'target_url' => $this->true_link,
            'custom_alias' => $this->new_link,
            'short_url' => url('/'.$this->new_link),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
