<?php

namespace voskobovich\rest;

use voskobovich\data\CollectionProviderInterface;
use yii\base\Arrayable;
use yii\base\Model;
use yii\data\DataProviderInterface;


/**
 * Class Serializer
 * @package voskobovich\rest
 */
class Serializer extends \yii\rest\Serializer
{
    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } elseif ($data instanceof CollectionProviderInterface) {
            return $this->serializeCollectionProvider($data);
        } else {
            return $data;
        }
    }

    /**
     * Serializes a data provider.
     * @param CollectionProviderInterface $collectionProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeCollectionProvider($collectionProvider)
    {
        $models = $this->serializeModels($collectionProvider->getModels());

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            return [
                $this->collectionEnvelope => $models,
                $this->metaEnvelope => [
                    'count' => $collectionProvider->getCount(),
                    'totalCount' => $collectionProvider->getTotalCount(),
                ]
            ];
        }
    }
}