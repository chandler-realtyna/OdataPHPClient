<?php

namespace Realtyna\OData;

class ODataQueryBuilder
{
    private string $baseUri;
    private ODataFilterBuilder $filterBuilder;
    private ODataQueryOptions $queryOptions;

    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri;
        $this->filterBuilder = new ODataFilterBuilder();
        $this->queryOptions = new ODataQueryOptions();
    }

    /**
     * Set the filter condition using the filter builder.
     *
     * @param string $field The field to filter on.
     * @param string $operator The comparison operator (e.g., 'eq', 'ne', 'lt', 'gt', 'le', 'ge').
     * @param mixed $value The value to compare against.
     * @param string $logical The logical operator ('and' or 'or') to combine with the previous condition.
     * @param string|null $function The filter function, e.g., 'contains', 'startswith', 'endswith', 'length', or null for default.
     *
     * @return $this
     */
    public function addFilter($field, $operator, $value, $logical = 'and', $function = null): static
    {
        if ($function === 'contains') {
            $this->filterBuilder->contains($field, $value, $logical);
        } elseif ($function === 'startswith') {
            $this->filterBuilder->startswith($field, $value, $logical);
        } elseif ($function === 'endswith') {
            $this->filterBuilder->endswith($field, $value, $logical);
        } elseif ($function === 'length') {
            $this->filterBuilder->length($field, $value, $operator, $logical);
        } else {
            $this->filterBuilder->where($field, $operator, $value, $logical);
        }

        return $this;
    }


    /**
     * Set the $select option for the query.
     *
     * @param array $fields Fields to be selected.
     *
     * @return $this
     */
    public function select(array $fields): static
    {
        $this->queryOptions->select($fields);
        return $this;
    }

    /**
     * Set the $expand option for the query.
     *
     * @param array $fields Fields to be expanded.
     *
     * @return $this
     */
    public function expand(array $fields): static
    {
        $this->queryOptions->expand($fields);
        return $this;
    }

    /**
     * Set the $orderby option for the query.
     *
     * @param array $fields Fields to be ordered by and their sorting direction.
     *
     * @return $this
     */
    public function orderBy(array $fields): static
    {
        $this->queryOptions->orderBy($fields);
        return $this;
    }

    /**
     * Set the $top option for the query.
     *
     * @param int $count The maximum number of records to return.
     *
     * @return $this
     */
    public function top(int $count): static
    {
        $this->queryOptions->top($count);
        return $this;
    }

    /**
     * Set the $skip option for the query.
     *
     * @param int $count The number of records to skip before starting to return records.
     *
     * @return $this
     */
    public function skip(int $count): static
    {
        $this->queryOptions->skip($count);
        return $this;
    }

    /**
     * Get the query options.
     *
     * @return ODataQueryOptions
     */
    public function getQueryOptions(): ODataQueryOptions
    {
        return $this->queryOptions;
    }

    /**
     * Build and return the complete query URL.
     *
     * @return string The complete query URL.
     */
    public function buildQueryUrl(): string
    {
        $baseUri = rtrim($this->baseUri, '/');
        $queryOptions = $this->queryOptions->buildQuery();
        $filterExpression = $this->filterBuilder->getFilterExpression();

        $queryUrl = $baseUri;

        if (!empty($queryOptions)) {
            $queryUrl .= '?' . $queryOptions;
        }

        if (!empty($filterExpression)) {
            $queryUrl .= '&$filter=' . $filterExpression;
        }

        return $queryUrl;
    }
}
