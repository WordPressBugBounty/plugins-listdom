<?php

/**
 * @method response($response)
 * @method request(string $prompt, float $temperature = 0.2, string $system = '')
 */
trait LSD_AI_Tasks_Mapping
{
    public function auto_mapping(array $listdom_fields, array $source_fields): array
    {
        $listdom_fields_compact = [];
        foreach ($listdom_fields as $field_key => $listdom_field)
        {
            $listdom_fields_compact[$field_key] = [
                'label' => $listdom_field['label'] ?? '',
                'type' => $listdom_field['type'] ?? 'text',
            ];
        }

        $listdom_fields_json = json_encode($listdom_fields_compact, JSON_PRETTY_PRINT);
        $source_fields_json = json_encode($source_fields, JSON_PRETTY_PRINT);

        $prompt = 'You are an intelligent field-mapping assistant. Your task is to map Listdom fields to the most appropriate column indices from a provided CSV or Excel file.

        Instructions:
        - Read both the Listdom fields and the CSV/Excel column titles. Evaluate each column individually, regardless of its position in the file. Do not skip valid columns based on their order.
        - Always prioritize **exact or very close name matches** (e.g., "name" → "post_title", "city" → "locations").
        - Ignore any CSV/Excel columns that are empty or unnamed.
          In the JSON input, such columns may appear as an empty string (""), null, or whitespace-only.
          Do not map these columns to any Listdom field.
        - Map each Listdom field to the most relevant CSV column index.
        - Use semantic similarity to guide mappings. For example:
          - "name" or "listing name" → "post_title"
          - "places" or "city" → "locations"
        - If a clear match cannot be found, exclude that field from the mapping.

        Inputs:
        Listdom Fields (JSON):
        ' . $listdom_fields_json . '

        CSV/Excel Columns (first row as JSON):
        ' . $source_fields_json . '

        Output:
        Generate a clean JSON object where the key is the Listdom field name and the value is the corresponding CSV column index.
        Only include fields that have a valid match and are not based on empty or unnamed columns.

        Example format:
        {"unique_id":0,"post_title":3,"post_name":5}

        Return only the JSON object without any explanation or extra text.';

        $response = $this->request($prompt);

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return [];

        return $this->response($response);
    }
}
