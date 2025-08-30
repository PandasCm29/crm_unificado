<?php

class NotionPropertyGetter
{
    private array $properties;

    /**
     * @param array $properties El array completo de propiedades de la API de Notion.
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Obtiene el valor de una propiedad por su nombre.
     *
     * @param string $propertyName El nombre de la propiedad.
     * @return mixed|null El valor de la propiedad o null si no se encuentra.
     */
    public function get(string $propertyName): mixed
    {
        // Verifica si la propiedad existe en el array
        if (!isset($this->properties[$propertyName])) {
            return null;
        }

        $property = $this->properties[$propertyName];
        $propertyType = $property['type'];

        // Llama al método estático correspondiente según el tipo
        return match ($propertyType) {
            'title' => $this->title($property),
            'relation' => $this->relation($property),
            'rich_text' => $this->richText($property),
            'number' => $this->number($property),
            'phone_number' => $this->phoneNumber($property),
            'select' => $this->select($property),
            'checkbox' => $this->checkbox($property),
            'multi_select' => $this->multiSelect($property),
            'date' => $this->date($property),
            'email' => $this->email($property),
            'status' => $this->status($property),
            default => null, // Devuelve null si el tipo de propiedad no es compatible
        };
    }
    private function title(array $property): ?string
    {
        // La propiedad 'title' es un array de objetos de rich_text
        return $property['title'][0]['text']['content'] ?? null;
    }

    private function relation(array $property): array  
    {
        // 'relation' es un array de IDs de las páginas relacionadas
        if ($property && $property['type'] === 'relation') {
            // Devuelve el array de IDs o un array vacío si no hay relaciones
            return array_column($property['relation'], 'id');
        }
        return [];
    }

    private function richText(array $property): ?string
    {
        // 'rich_text' también es un array de objetos
        return $property['rich_text'][0]['text']['content'] ?? "";
    }

    private function number(array $property): ?float
    {
        // 'number' es un valor directo o null
        return $property['number'] ?? null;
    }

    private function phoneNumber(array $property): ?string
    {
        // 'phone_number' es un valor directo o null
        return $property['phone_number'] ?? null;
    }

    private function select(array $property): ?string
    {
        // 'select' es un objeto con la propiedad 'name'
        return $property['select']['name'] ?? null;
    }

    private function checkbox(array $property): bool
    {
        // 'checkbox' es un booleano directo
        return $property['checkbox'] ?? false;
    }

    private function multiSelect(array $property): array
    {
        // 'multi_select' es un array de objetos con 'name'
        return array_column($property['multi_select'], 'name');
    }

    private function date(array $property): ?array
    {
        // 'date' es un objeto con las propiedades 'start' y 'end'
        return $property['date'] ?? null;
    }

    private function email(array $property): ?string
    {
        // 'email' es un valor directo o null
        return $property['email'] ?? null;
    }

    private function status(array $property): ?string
    {
        // 'status' es un objeto con la propiedad 'name'
        return $property['status']['name'] ?? null;
    }
}