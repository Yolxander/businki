<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'template',
        'is_active',
    ];

    /**
     * Render the template with the given data (replace {variable} with values)
     */
    public function renderPrompt(array $data): string
    {
        return preg_replace_callback('/{(\w+)}/', function ($matches) use ($data) {
            return $data[$matches[1]] ?? '';
        }, $this->template);
    }
}
