<?php

namespace Realtyna\OData;

class ODataFilterBuilder
{
    private string $filterExpression;
    private string $currentBoolean = 'and';
    private string $state;

    public function __construct()
    {
        $this->filterExpression = '';
    }

    /**
     * Add a filter condition.
     *
     * @param array|string $field The field to filter on, or an array of nested conditions.
     * @param string|null $operator The comparison operator (e.g., 'eq', 'ne', 'lt', 'gt', 'le', 'ge').
     * @param mixed|null $value The value to compare against (ignored when nested conditions are provided).
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function where(
        array|string $field,
        string $operator = null,
        mixed $value = null,
        string $logical = 'and'
    ): static {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        if (is_array($field)) {
            // Handle nested conditions
            $nestedExpression = $this->buildNestedConditions($field, $logical);
            $this->filterExpression .= "($nestedExpression)";
        } else {
            // Handle regular conditions
            $escapedField = $this->escapeField($field);
            $escapedValue = $this->escapeValue($value);

            $this->filterExpression .= "$escapedField $operator $escapedValue";
        }

        return $this;
    }

    /**
     * Start a filter group.
     *
     * @param string $relation The logical relation (AND/OR) to combine with the previous condition.
     *
     * @return $this
     */
    public function startGroup($relation = 'AND'): static
    {
        $this->currentBoolean = $relation;
        $this->state = 'started';
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " AND (";
        } else {
            $this->filterExpression .= '(';
        }

        return $this;
    }

    /**
     * End the current filter group.
     *
     * @return $this
     */
    public function endGroup(): static
    {
        $this->filterExpression .= ')';

        return $this;
    }

    /**
     * Build nested conditions from an array.
     *
     * @param array $conditions An array of nested conditions.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return string
     */
    private function buildNestedConditions(array $conditions, string $logical): string
    {
        $nestedExpression = '';

        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = $condition['value'];

            if ($nestedExpression !== '') {
                $nestedExpression .= " $logical ";
            }

            $escapedField = $this->escapeField($field);
            $escapedValue = $this->escapeValue($value);

            $nestedExpression .= "$escapedField $operator $escapedValue";
        }

        return $nestedExpression;
    }

    /**
     * Get the constructed filter expression.
     *
     * @return string
     */
    public function getFilterExpression(): string
    {
        return $this->filterExpression;
    }

    /**
     * Escape a field name for use in the filter expression.
     *
     * @param string|array $field
     *
     * @return string|array
     */
    private function escapeField(string|array $field): string|array
    {
        // You may need to implement field escaping logic specific to your OData service.
        return $field;
    }

    /**
     * Escape a value for use in the filter expression.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function escapeValue(mixed $value)
    {
        // You may need to implement value escaping logic specific to your OData service.
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if (is_array($value)) {
            return "'" . implode(',', $value) . "'";
        }

        return $value;
    }

    /**
     * Adds a WHERE IN clause to the filter expression.
     *
     * @param string $field The field to filter on.
     * @param array $values An array of values for the WHERE IN clause.
     * @param string $logical The logical operator to use for combining with previous conditions (default: 'and').
     *
     * @return static $this
     */
    public function whereIn(string $field, array $values, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);
        $escapedValues = implode(', ', array_map([$this, 'escapeValue'], $values));

        $this->filterExpression .= "$escapedField in ($escapedValues)";

        return $this;
    }

    /**
     * Add a filter condition using the 'contains' function.
     *
     * @param string $field The field to filter on.
     * @param mixed $value The substring to check for.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function contains(string $field, mixed $value, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);
        $escapedValue = $this->escapeValue($value);

        $this->filterExpression .= "contains($escapedField, $escapedValue)";

        return $this;
    }

    /**
     * Adds a distance filter to the OData query expression.
     *
     * @param string $field The field representing coordinates in the data.
     * @param string|null $operator The comparison operator for the distance filter (e.g., 'le' for less than or equal to).
     * @param mixed $value The values for latitude, longitude, and radius in an associative array.
     *                     Example: ['lat' => '37.9020731', 'long' => '-122.0618702', 'radius' => '10']
     * @param string $logical The logical operator to use for combining with other conditions (default is 'and').
     *
     * @return static Returns an instance of the ODataQueryBuilder for method chaining.
     */
    public function distance(string $field, string $operator = null, mixed $value, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        // If latitude, longitude, and radius are not set, no distance filter is applied
        if (!isset($value['lat']) && !isset($value['long']) && !isset($value['radius'])) {
            return $this;
        }

        $escapedLat = $this->escapeValue($value['lat']);
        $escapedLong = $this->escapeValue($value['long']);
        $radius = $this->escapeValue($value['radius']);

        $this->filterExpression .= "geo.distance($field, POINT($escapedLong $escapedLat)) $operator $radius";

        return $this;
    }



    /**
     * Add a filter condition using the 'substringof' function.
     *
     * @param string $substring The substring to check for.
     * @param string $field The field that should contain the substring.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function substringof(string $substring, string $field, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "substringof($escapedSubstring, $escapedField)";

        return $this;
    }

    /**
     * Add a filter condition using the 'startswith' function.
     *
     * @param string $field The field to filter on.
     * @param string $substring The substring to check for at the beginning.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function startswith(string $field, string $substring, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "startswith($escapedField, $escapedSubstring)";

        return $this;
    }

    /**
     * Add a filter condition using the 'endswith' function.
     *
     * @param string $field The field to filter on.
     * @param string $substring The substring to check for at the end.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function endswith(string $field, string $substring, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "endswith($escapedField, $escapedSubstring)";

        return $this;
    }

    /**
     * Add a filter condition using the 'length' function.
     *
     * @param string $field The field whose length you want to check.
     * @param int $length The length to compare against.
     * @param string $comparison The comparison operator ('eq', 'ne', 'lt', 'le', 'gt', 'ge').
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function length(string $field, int $length, string $comparison = 'eq', string $logical = 'and'): static
    {
        if ($this->filterExpression !== '' && $this->state != 'started') {
            $this->filterExpression .= " $this->currentBoolean ";
        }
        $this->state = 'middle';

        $escapedField = $this->escapeField($field);

        $this->filterExpression .= "length($escapedField) $comparison $length";

        return $this;
    }
}
