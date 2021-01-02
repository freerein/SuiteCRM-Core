<?php

namespace App\Legacy\Data;

use App\Legacy\Data\FilterMapper\LegacyFilterMapper;
use BeanFactory;
use InvalidArgumentException;
use ListViewDataPort;
use SearchForm;
use SugarBean;
use ViewList;

abstract class BaseListDataHandler
{
    /**
     * @var LegacyFilterMapper
     */
    protected $legacyFilterMapper;

    /**
     * @var RecordMapper
     */
    protected $recordMapper;

    /**
     * ListDataHandler constructor.
     * @param LegacyFilterMapper $legacyFilterMapper
     * @param RecordMapper $recordMapper
     */
    public function __construct(
        LegacyFilterMapper $legacyFilterMapper,
        RecordMapper $recordMapper
    ) {
        $this->legacyFilterMapper = $legacyFilterMapper;
        $this->recordMapper = $recordMapper;
    }

    /**
     * @return ListViewDataPort
     */
    protected function getListDataPort(): ListViewDataPort
    {
        /* @noinspection PhpIncludeInspection */
        require_once 'include/portability/ListView/ListViewDataPort.php';

        return new ListViewDataPort();
    }

    /**
     * @param string $module
     *
     * @return SugarBean
     * @throws InvalidArgumentException When the module is invalid.
     */
    protected function getBean(string $module): SugarBean
    {
        $bean = BeanFactory::newBean($module);

        if (!$bean instanceof SugarBean) {
            throw new InvalidArgumentException(sprintf('Module %s does not exist', $module));
        }

        return $bean;
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param string $type
     * @return array
     */
    protected function mapCriteria(array $criteria, array $sort, string $type): array
    {
        $mapped = $this->legacyFilterMapper->mapFilters($criteria, $type);

        $baseCriteria = [
            'searchFormTab' => "${type}_search",
            'query' => 'true',
            'orderBy' => $this->legacyFilterMapper->getOrderBy($sort),
            'sortOrder' => $this->legacyFilterMapper->getSortOrder($sort)
        ];

        return array_merge($baseCriteria, $mapped);
    }

    /**
     * @param string $type
     * @param SugarBean $bean
     * @param array $criteria
     * @return array
     */
    protected function prepareQueryData(string $type, SugarBean $bean, array $criteria): array
    {
        $legacyListView = $this->getLegacyListView($bean);
        $listViewDefs = $this->getListViewDefs($legacyListView);
        $searchForm = $this->getSearchForm($type, $bean, $listViewDefs, $criteria);
        $params = $this->getSortingParams($criteria);

        $where = $this->buildFilterClause($bean, $searchForm);

        $filter_fields = $legacyListView->lv->setupFilterFields([]);

        return [$params, $where, $filter_fields];
    }

    /**
     * @param SugarBean $bean
     * @return ViewList
     */
    protected function getLegacyListView(SugarBean $bean): ViewList
    {
        $legacyListView = new ViewList();
        $legacyListView->bean = $bean;
        $legacyListView->module = $bean->module_name;
        $legacyListView->preDisplay();

        return $legacyListView;
    }

    /**
     * Get list view defs
     * @param ViewList $legacyListView
     * @return array
     */
    protected function getListViewDefs(ViewList $legacyListView): array
    {
        $listViewDefs = [];
        $metadataFile = $legacyListView->getMetaDataFile();

        /* @noinspection PhpIncludeInspection */
        require $metadataFile;

        return $listViewDefs;
    }

    /**
     * @param string $type
     * @param SugarBean $bean
     * @param array $listViewDefs
     * @param array $criteria
     * @return SearchForm
     */
    protected function getSearchForm(
        string $type,
        SugarBean $bean,
        array $listViewDefs,
        array $criteria = []
    ): SearchForm {

        /* @noinspection PhpIncludeInspection */
        require_once 'include/SearchForm/SearchForm2.php';
        $searchMetaData = SearchForm::retrieveSearchDefs($bean->module_name);
        $searchForm = new SearchForm($bean, $bean->module_name, 'index');
        $searchForm->setup(
            $searchMetaData['searchdefs'],
            $searchMetaData['searchFields'],
            'SearchFormGeneric.tpl',
            $type . '_search',
            $listViewDefs
        );

        $searchForm->populateFromArray($criteria);

        return $searchForm;
    }

    /**
     * Get Legacy sorting parameters
     * @param array $criteria
     * @return array
     */
    protected function getSortingParams(array $criteria): array
    {
        $params = [];
        if (!empty($criteria['orderBy'])) {
            $params = [
                'orderBy' => strtoupper($criteria['orderBy']),
                'sortOrder' => $criteria['sortOrder'] ?? '',
                'skipOrderSave' => true,
                'overrideOrder' => true,
                'custom_order' => true
            ];
        }

        return $params;
    }

    /**
     * @param SugarBean $bean
     * @param SearchForm $searchForm
     * @return string
     */
    protected function buildFilterClause(SugarBean $bean, SearchForm $searchForm): string
    {
        $where_clauses = $searchForm->generateSearchWhere(true, $bean->module_dir);

        $where = '';
        if (count($where_clauses) > 0) {
            $where = '(' . implode(' ) AND ( ', $where_clauses) . ')';
        }

        return $where;
    }
}
