<?php

/**
 * @method response($response)
 * @method request(string $prompt, float $temperature = 0.2, string $system = '')
 */
trait LSD_AI_Tasks_Structured_Search
{
    public function structured_search(string $query, array $schema): array
    {
        $schema_json = wp_json_encode($schema);
        $query_preview = function_exists('mb_substr') ? mb_substr($query, 0, 300) : substr($query, 0, 300);

        $prompt = sprintf(<<<'PROMPT'
            You convert natural-language directory searches into Listdom filter JSON.

            Instructions:
            - Use only taxonomy IDs, attribute keys, and attribute values from the provided schema.
            - Never invent IDs, keys, operators, or values.
            - Schema notes:
              - taxonomies[taxonomy].l is the taxonomy label.
              - taxonomies[taxonomy].t is an id:name map. Use the IDs in your output.
              - attributes[key].l is the field label or hint.
              - attributes[key].m can be "single", "multiple", or "range". If missing, treat it as free text.
              - attributes[key].v lists allowed output values when present.
            - Put taxonomy filters in "taxonomies" as taxonomy_key => [ids].
            - Put attribute filters in "attributes" as exact query keys => values.
            - Use strings for single-value attributes and arrays for multi-value attributes.
            - For range attributes, return a numeric "min:max" string.
            - When an attribute label describes a 1 to 5 review rating, use a high minimum such as 4 or 5 for positive requirements and a low maximum such as 1 or 2 for negative requirements.
            - Prefer "leftover" over risky guesses. If a phrase sounds descriptive, qualitative, or semantic and does not map cleanly to an explicit schema value, keep it in "leftover".
            - Do not turn natural-language phrases into custom-field filters unless the schema clearly supports that exact kind of value.
            - If part of the query cannot be converted safely, keep it in "leftover".
            - If nothing is reliable enough to convert, leave taxonomies and attributes empty and put the original intent in "leftover".

            Output format:
            {
              "taxonomies": {},
              "attributes": {},
              "leftover": ""
            }

            Search Query:
            %s

            Schema:
            %s

            Return only the JSON object with no explanation.
            PROMPT,
            $query_preview,
            $schema_json
        );

        $response = $this->request(
            $prompt,
            0.1,
            'You are a strict JSON generator for directory search filters. Return only valid JSON and never add commentary.'
        );

        if (isset($response['error']) && is_array($response['error']) && count($response['error'])) return [];

        return $this->response($response);
    }
}
