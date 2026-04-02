<?php
function cleanInput(?string $value): string
{
    return trim((string)$value);
}

function formatAmount(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function getCategories(): array
{
    return [
        'Food',
        'Transport',
        'Study Materials',
        'Entertainment',
        'Hostel',
        'Health',
        'Other',
    ];
}
