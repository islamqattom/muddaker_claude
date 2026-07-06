<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'category',
    ];

    protected $appends = ['url', 'download_url', 'human_size', 'is_image', 'is_video', 'is_audio', 'icon'];

    public const CATEGORY_IMAGE = 'image';
    public const CATEGORY_VIDEO = 'video';
    public const CATEGORY_AUDIO = 'audio';
    public const CATEGORY_DOCUMENT = 'document';
    public const CATEGORY_FILE = 'file';

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('chat.attachment.download', $this);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return $this->category === self::CATEGORY_IMAGE
            || Str::startsWith((string) $this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->category === self::CATEGORY_VIDEO
            || Str::startsWith((string) $this->mime_type, 'video/');
    }

    public function getIsAudioAttribute(): bool
    {
        return $this->category === self::CATEGORY_AUDIO
            || Str::startsWith((string) $this->mime_type, 'audio/');
    }

    public function getIconAttribute(): string
    {
        if ($this->is_image) return 'fa-image';
        if ($this->is_video) return 'fa-video';
        if ($this->is_audio) return 'fa-music';
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        return match (true) {
            in_array($ext, ['pdf'], true) => 'fa-file-pdf',
            in_array($ext, ['doc', 'docx', 'rtf', 'odt'], true) => 'fa-file-word',
            in_array($ext, ['xls', 'xlsx', 'ods', 'csv'], true) => 'fa-file-excel',
            in_array($ext, ['ppt', 'pptx', 'odp'], true) => 'fa-file-powerpoint',
            in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'], true) => 'fa-file-zipper',
            in_array($ext, ['txt', 'log', 'md'], true) => 'fa-file-lines',
            default => 'fa-file',
        };
    }

    public function detectCategory(): string
    {
        $mime = (string) $this->mime_type;
        if (Str::startsWith($mime, 'image/')) return self::CATEGORY_IMAGE;
        if (Str::startsWith($mime, 'video/')) return self::CATEGORY_VIDEO;
        if (Str::startsWith($mime, 'audio/')) return self::CATEGORY_AUDIO;
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt', 'ods', 'odp', 'md'], true)) {
            return self::CATEGORY_DOCUMENT;
        }
        return self::CATEGORY_FILE;
    }
}
