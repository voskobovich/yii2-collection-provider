<?php

namespace voskobovich\rest\data;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\data\Sort;


/**
 * Class BaseCollectionProvider
 * @package voskobovich\rest\data
 */
abstract class BaseCollectionProvider extends Component implements CollectionProviderInterface
{
    /**
     * @var string an ID that uniquely identifies the data provider among all data providers.
     * You should set this property if the same page contains two or more different data providers.
     * Otherwise, the [[sort]] mainly not work properly.
     */
    public $id;

    private $_sort;
    private $_keys;
    private $_models;
    private $_totalCount;


    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    abstract protected function prepareModels();

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    abstract protected function prepareKeys($models);

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return integer total number of data models in this data provider.
     */
    abstract protected function prepareTotalCount();

    /**
     * Prepares the data models and keys.
     *
     * This method will prepare the data models and keys that can be retrieved via
     * [[getModels()]] and [[getKeys()]].
     *
     * This method will be implicitly called by [[getModels()]] and [[getKeys()]] if it has not been called before.
     *
     * @param boolean $forcePrepare whether to force data preparation even if it has been done before.
     */
    public function prepare($forcePrepare = false)
    {
        if ($forcePrepare || $this->_models === null) {
            $this->_models = $this->prepareModels();
        }
        if ($forcePrepare || $this->_keys === null) {
            $this->_keys = $this->prepareKeys($this->_models);
        }
    }

    /**
     * Returns the data models in the current page.
     * @return array the list of data models in the current page.
     */
    public function getModels()
    {
        $this->prepare();

        return $this->_models;
    }

    /**
     * Sets the data models in the current page.
     * @param array $models the models in the current page
     */
    public function setModels($models)
    {
        $this->_models = $models;
    }

    /**
     * Returns the key values associated with the data models.
     * @return array the list of key values corresponding to [[models]]. Each data model in [[models]]
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys()
    {
        $this->prepare();

        return $this->_keys;
    }

    /**
     * Sets the key values associated with the data models.
     * @param array $keys the list of key values corresponding to [[models]].
     */
    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    /**
     * Returns the number of data models in the current page.
     * @return integer the number of data models in the current page.
     */
    public function getCount()
    {
        return count($this->getModels());
    }

    /**
     * Returns the total number of data models.
     * This returns the same value as [[count]].
     * Otherwise, it will call [[prepareTotalCount()]] to get the count.
     * @return integer total number of possible data models.
     */
    public function getTotalCount()
    {
        if ($this->_totalCount === null) {
            $this->_totalCount = $this->prepareTotalCount();
        }

        return $this->_totalCount;
    }

    /**
     * Sets the total number of data models.
     * @param integer $value the total number of data models.
     */
    public function setTotalCount($value)
    {
        $this->_totalCount = $value;
    }

    /**
     * @return Sort|boolean the sorting object. If this is false, it means the sorting is disabled.
     */
    public function getSort()
    {
        if ($this->_sort === null) {
            $this->setSort([]);
        }

        return $this->_sort;
    }

    /**
     * Sets the sort definition for this data provider.
     * @param array|Sort|boolean $value the sort definition to be used by this data provider.
     * This can be one of the following:
     *
     * - a configuration array for creating the sort definition object. The "class" element defaults
     *   to 'yii\data\Sort'
     * - an instance of [[Sort]] or its subclass
     * - false, if sorting needs to be disabled.
     *
     * @throws InvalidParamException
     */
    public function setSort($value)
    {
        if (is_array($value)) {
            $config = ['class' => Sort::className()];
            if ($this->id !== null) {
                $config['sortParam'] = $this->id . '-sort';
            }
            $this->_sort = Yii::createObject(array_merge($config, $value));
        } elseif ($value instanceof Sort || $value === false) {
            $this->_sort = $value;
        } else {
            throw new InvalidParamException('Only Sort instance, configuration array or false is allowed.');
        }
    }

    /**
     * Refreshes the data provider.
     * After calling this method, if [[getModels()]], [[getKeys()]] or [[getTotalCount()]] is called again,
     * they will re-execute the query and return the latest data available.
     */
    public function refresh()
    {
        $this->_totalCount = null;
        $this->_models = null;
        $this->_keys = null;
    }
}