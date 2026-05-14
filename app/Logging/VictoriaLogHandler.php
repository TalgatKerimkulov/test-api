<?php

declare(strict_types=1);

namespace App\Logging;

use App\Services\Victoria\VictoriaService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class VictoriaLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly VictoriaService $victoriaService,
        Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        try {
            $this->victoriaService->send([
                'time' => now()->toIso8601String(),
                'level' => $record->level->getName(),
                'channel' => $record->channel,
                'message' => $record->message,
                'context' => $record->context,
            ]);
        } catch (\Throwable) {
            // Never break app flow if external logging fails.
        }
    }
}

