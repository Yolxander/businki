<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ContextEngineeringDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'dev_project_id',
        'name',
        'description',
        'type',
        'content',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_generated',
        'generation_metadata',
        'variables',
        'is_template',
        'is_active',
        'version',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_generated' => 'boolean',
        'generation_metadata' => 'array',
        'variables' => 'array',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'version' => 'integer'
    ];

    // Document types
    const TYPE_IMPLEMENTATION = 'implementation';
    const TYPE_WORKFLOW = 'workflow';
    const TYPE_PROJECT_STRUCTURE = 'project_structure';
    const TYPE_UI_UX = 'ui_ux';
    const TYPE_BUG_TRACKING = 'bug_tracking';
    const TYPE_CUSTOM = 'custom';

    public static function getTypes(): array
    {
        return [
            self::TYPE_IMPLEMENTATION => 'Implementation Plan',
            self::TYPE_WORKFLOW => 'Development Workflow',
            self::TYPE_PROJECT_STRUCTURE => 'Project Structure',
            self::TYPE_UI_UX => 'UI/UX Documentation',
            self::TYPE_BUG_TRACKING => 'Bug Tracking',
            self::TYPE_CUSTOM => 'Custom Document'
        ];
    }

    // Relationships
    public function devProject(): BelongsTo
    {
        return $this->belongsTo(DevProject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContextEngineeringDocument::class, 'dev_project_id', 'dev_project_id')
            ->where('name', $this->name)
            ->where('type', $this->type);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeGenerated($query)
    {
        return $query->where('is_generated', true);
    }

    // Methods
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            return Storage::disk('local')->url($this->file_path);
        }
        return null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function createNewVersion(): self
    {
        $newVersion = $this->replicate();
        $newVersion->version = $this->version + 1;
        $newVersion->is_active = false;
        $newVersion->save();

        return $newVersion;
    }

    public function activate(): void
    {
        // Deactivate all other versions of this document
        self::where('dev_project_id', $this->dev_project_id)
            ->where('name', $this->name)
            ->where('type', $this->type)
            ->update(['is_active' => false]);

        // Activate this version
        $this->update(['is_active' => true]);
    }

    public function generateFileName(): string
    {
        $extension = $this->getFileExtension();
        return sprintf(
            '%s_%s_v%d.%s',
            str_replace(' ', '_', $this->name),
            $this->type,
            $this->version,
            $extension
        );
    }

    private function getFileExtension(): string
    {
        return match ($this->type) {
            self::TYPE_IMPLEMENTATION, self::TYPE_WORKFLOW, self::TYPE_PROJECT_STRUCTURE,
            self::TYPE_UI_UX, self::TYPE_BUG_TRACKING => 'md',
            default => 'txt'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Unknown';
    }

    public function isMarkdown(): bool
    {
        return in_array($this->type, [
            self::TYPE_IMPLEMENTATION,
            self::TYPE_WORKFLOW,
            self::TYPE_PROJECT_STRUCTURE,
            self::TYPE_UI_UX,
            self::TYPE_BUG_TRACKING
        ]);
    }
}
