<?php

namespace Maze;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
    /**
     * Number of elements per page.
     *
     * @var int
     */
    public $per_page;

    /**
     * Content of all http client request data.
     *
     * @var Request
     */
    protected $request;

    /**
     * Eloquent ORM builder.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * QueryFilters constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->per_page = $this->perPage();
    }

    /**
     * Apply the filters to the builder.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $name => $value) {
            if (! method_exists($this, $name)) {
                continue;
            }
            if (strlen($value)) {
                $this->$name($value);
            } else {
                $this->$name();
            }
        }
        return $this->builder;
    }

    /**
     * Get per page
     * @return int
     */
    protected function perPage()
    {
        return collect([5, 10, 15, 25, 50])
            ->contains((int) request()->input('per_page')) ? (int) request()->input('per_page') : 10;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function order($value)
    {
        if (! $value && ! strpos($value, '.'))
            return $this->builder;

        if (strpos($value, ':')) {
            $result = explode(':', $value);
            return $this->builder->orderBy($result[0], $result[1]);
        }

        $result = explode('.', $value);
        return $this->builder->orderBy($result[0], $result[1]);
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    protected function like($name, $value)
    {
        if (! $value) return $this->builder;

        return $this->builder->where($name, 'like', "%{$value}%");
    }

    /**
     * Get all request filters data.
     *
     * @return array
     */
    protected function filters()
    {
        return $this->request->all();
    }
}
