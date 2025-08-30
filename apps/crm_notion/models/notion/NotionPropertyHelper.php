<?php

class NotionPropertyHelper
{
    public static function title(?string $text): array
    {
        return ['title' => empty($text) ? null : [['text' => ['content' => $text]]]];
    }

    public static function relation(array $ids): array
    {
        return ['relation' => array_map(fn($id) => ['id' => $id], $ids)];
    }

    public static function richText(?string $text): array
    {
        return ['rich_text' => empty($text) ? [] : [['text' => ['content' => $text]]]];
    }

    public static function number(?float $value): array
    {
        return ['number' => $value === [] ? null : $value];
    }

    public static function phone_number(?string $value): array
    {
        return ['phone_number' => empty($value) ? null : $value];
    }

    public static function select(?string $option): array
    {
        return ['select' => empty($option) ? null : ['name' => $option]];
    }
    public static function checkbox(?bool $value): array
    {
        // El valor por defecto para una casilla vacía es `false`
        return ['checkbox' => $value ?? false];
    }

    public static function multiSelect(array $options): array
    {
        return ['multi_select' => empty($options) ? [] : array_map(fn($o) => ['name' => $o], $options)];
    }

    public static function date(?array $date): array
    {
        // $date debe tener al menos 'start' o Notion no lo acepta
        return ['date' => empty($date) ? null : $date];
    }

    public static function email(?string $value): array
    {
        return ['email' => empty($value) ? null : $value];
    }

    public static function status(?string $value): array
    {
        return ['status' => empty($value) ? null : ['name' => $value]];
    }

    public static function icon(?string $url): array
    {
        return ['external' => ['url' => empty($url) ? null : $url]];
    }
}
