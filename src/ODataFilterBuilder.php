<?php

namespace Realtyna\OData;

class ODataFilterBuilder
{
    private string $filterExpression;

    public function __construct()
    {
        $this->filterExpression = '';
    }

    /**
     * Add a filter condition.
     *
     * @param array|string $field     The field to filter on, or an array of nested conditions.
     * @param string|null $operator  The comparison operator (e.g., 'eq', 'ne', 'lt', 'gt', 'le', 'ge').
     * @param mixed|null $value     The value to compare against (ignored when nested conditions are provided).
     * @param string $logical   The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function where(array|string $field, string $operator = null, mixed $value = null, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

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
     * Build nested conditions from an array.
     *
     * @param array  $conditions  An array of nested conditions.
     * @param string $logical     The logical operator ('and' or 'or') to combine with the previous condition.
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
     * @param string $field
     *
     * @return string
     */
    private function escapeField(string $field): string
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
    private function escapeValue(mixed $value): string
    {
        // You may need to implement value escaping logic specific to your OData service.
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }

        return $value;
    }

    /**
     * Add a filter condition using the 'contains' function.
     *
     * @param string $field     The field to filter on.
     * @param mixed  $value     The substring to check for.
     * @param string $logical   The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function contains(string $field, mixed $value, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

        $escapedField = $this->escapeField($field);
        $escapedValue = $this->escapeValue($value);

        $this->filterExpression .= "contains($escapedField, $escapedValue)";

        return $this;
    }

    /**
     * Add a filter condition using the 'substringof' function.
     *
     * @param string $substring  The substring to check for.
     * @param string $field      The field that should contain the substring.
     * @param string $logical    The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function substringof(string $substring, string $field, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "substringof($escapedSubstring, $escapedField)";

        return $this;
    }

    /**
     * Add a filter condition using the 'startswith' function.
     *
     * @param string $field     The field to filter on.
     * @param string $substring The substring to check for at the beginning.
     * @param string $logical   The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function startswith(string $field, string $substring, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "startswith($escapedField, $escapedSubstring)";

        return $this;
    }

    /**
     * Add a filter condition using the 'endswith' function.
     *
     * @param string $field     The field to filter on.
     * @param string $substring The substring to check for at the end.
     * @param string $logical   The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function endswith(string $field, string $substring, string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

        $escapedField = $this->escapeField($field);
        $escapedSubstring = $this->escapeValue($substring);

        $this->filterExpression .= "endswith($escapedField, $escapedSubstring)";

        return $this;
    }

    /**
     * Add a filter condition using the 'length' function.
     *
     * @param string $field     The field whose length you want to check.
     * @param int $length    The length to compare against.
     * @param string $comparison The comparison operator ('eq', 'ne', 'lt', 'le', 'gt', 'ge').
     * @param string $logical   The logical operator ('and' or 'or') to combine with the previous condition.
     *
     * @return $this
     */
    public function length(string $field, int $length, string $comparison = 'eq', string $logical = 'and'): static
    {
        if ($this->filterExpression !== '') {
            $this->filterExpression .= " $logical ";
        }

        $escapedField = $this->escapeField($field);

        $this->filterExpression .= "length($escapedField) $comparison $length";

        return $this;
    }
}