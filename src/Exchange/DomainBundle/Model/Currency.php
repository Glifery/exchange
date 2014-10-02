<?php

namespace Exchange\DomainBundle\Model;

class Currency
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $slug;

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    static function createFromArray(array $inputArray)
    {
        $inputSchema = array(
            'title',
            'code',
            'slug'
        );

        if (count($inputArray) != count($inputSchema)) {
            return null;
        }

        foreach ($inputSchema as $key) {
            if (!array_key_exists($key, $inputArray)) {
                return null;
            }
        }

        $currency = new Currency();

        foreach ($inputArray as $key => $value) {
            $currency->{'set'.ucfirst($key)}($value);
        }

        return $currency;
    }
}