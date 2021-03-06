<?php

namespace Devengine\RequestQueryBuilder\Tests;

use Devengine\RequestQueryBuilder\Contracts\SearchQueryProcessor;
use Devengine\RequestQueryBuilder\RequestQueryBuilder;
use Devengine\RequestQueryBuilder\Tests\Models\TestModel;
use Illuminate\Http\Request;

class QuickSearchTest extends TestCase
{
    public function test_it_can_add_where_clause_to_a_query_by_quick_search_fields()
    {
        $query = TestModel::query();
        $request = new Request([
            'search' => 'quick search input...',
        ]);

        $builder = RequestQueryBuilder::for(
            $query,
            $request,
        )->allowQuickSearchFields(...[
            'name',
        ])
            ->process();

        $this->assertSame('select * from `test_models` where (`name` like ?)', $builder->toSql());
        $this->assertNotEmpty($builder->getQuery()->bindings['where']);
        $this->assertSame('%quick search input...%', $builder->getQuery()->bindings['where'][0]);
    }

    public function test_it_can_add_where_clause_to_a_query_by_quick_search_fields_using_custom_search_query_processor()
    {
        $query = TestModel::query();
        $request = new Request([
            'search' => 'quick search input...',
        ]);

        $builder = RequestQueryBuilder::for(
            $query,
            $request,
        )->allowQuickSearchFields(...[
            'name',
        ])
            ->processSearchQueryWith(new class implements SearchQueryProcessor {

                public function __invoke(string $query, string $fieldName): string
                {
                    return "$query%";
                }

            })
            ->process();

        $this->assertSame('select * from `test_models` where (`name` like ?)', $builder->toSql());
        $this->assertNotEmpty($builder->getQuery()->bindings['where']);
        $this->assertSame('quick search input...%', $builder->getQuery()->bindings['where'][0]);
    }
}