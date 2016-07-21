<?php
declare(strict_types = 1);

namespace Siesta\Model;

/**
 * @author Gregor Müller
 */
class Index
{

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isUnique;

    /**
     * @var string
     */
    protected $indexType;

    /**
     * @var IndexPart[]
     */
    protected $indexPartList;

    /**
     * Index constructor.
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->indexPartList = [];
    }

    /**
     * @return IndexPart
     */
    public function newIndexPart() : IndexPart
    {
        $indexPart = new IndexPart($this->entity);
        $this->indexPartList[] = $indexPart;
        return $indexPart;
    }

    /**
     *
     */
    public function update()
    {
        foreach ($this->indexPartList as $indexPart) {
            $indexPart->update();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * @param boolean $isUnique
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;
    }

    /**
     * @return string
     */
    public function getIndexType()
    {
        return strtolower($this->indexType);
    }

    /**
     * @param string $indexType
     */
    public function setIndexType($indexType)
    {
        $this->indexType = $indexType;
    }

    /**
     * @return IndexPart[]
     */
    public function getIndexPartList()
    {
        return $this->indexPartList;
    }

}