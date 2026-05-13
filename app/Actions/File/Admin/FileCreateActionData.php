<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class FileCreateActionData
{
    public function __construct(
        public readonly UploadedFile $attachment,
        public readonly string $disk,
        public readonly string $type,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'attachment' => ['required', 'file', 'max:10240'],
            'disk' => ['nullable', 'string', 'max:32'],
            'type' => ['nullable', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $file = $request->file('attachment');
        if (! $file instanceof UploadedFile) {
            throw new ApiException('Attachment is required', 422);
        }

        return new self(
            attachment: $file,
            disk: (string) $request->input('disk', config('filesystems.default', 'public')),
            type: (string) $request->input('type', 'misc'),
        );
    }
}
