<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11/27/2015
 * Time: 1:04 PM
 */

namespace albaraam\push\services;


class BaseService
{
    /**
     * @var string push service id.
     * This value mainly used as HTTP request parameter.
     */
    private $_id;
    /**
     * @var string push service name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name;

    /**
     * @param string $id service id.
     */
    public function setId($id)
    {
        $this->_id = $id;
    }
    /**
     * @return string service id
     */
    public function getId()
    {
        if (empty($this->_id)) {
            $this->_id = $this->getName();
        }
        return $this->_id;
    }
    /**
     * @param string $name service name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }
    /**
     * @return string service name.
     */
    public function getName()
    {
        if ($this->_name === null) {
            $this->_name = $this->defaultName();
        }
        return $this->_name;
    }
}