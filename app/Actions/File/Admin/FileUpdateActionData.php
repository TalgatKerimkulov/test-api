<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class FileUpdateActionData
{
    public function __construct(
        public readonly int $id,
        public readonly ?UploadedFile $attachment,
        public readonly ?string $disk,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:files,id'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'disk' => ['nullable', 'string', 'max:32'],
        ]);

        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first(), 422);
        }

        $attachment = $request->file('attachment');
        if ($attachment !== null && ! $attachment instanceof UploadedFile) {
            throw new ApiException('Invalid attachment', 422);
        }

        return new self(
            id: (int) $request->integer('id'),
            attachment: $attachment,
            disk: $request->filled('disk') ? (string) $request->input('disk') : null,
        );
    }
}
