<?php

namespace App\Exceptions;

use Exception;

class InvalidStateTransitionException extends Exception
{
    public static function dariKe(string $statusAsal, string $aksi, string $modelType = ''): self
    {
        $label = $modelType ? " pada {$modelType}" : '';

        return new self(
            "Aksi '{$aksi}' tidak valid{$label} karena status saat ini adalah '{$statusAsal}'."
        );
    }
}
