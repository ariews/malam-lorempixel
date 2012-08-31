<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @author  arie
 */

abstract class Malam_Lorempixel
{
    const LPX_PATH              = ':protocol://lorempixel.com/:gray/:width/:height/';

    protected
        $_attributes        = NULL,
        $_uri_only          = FALSE,
        $_protocol          = 'http',
        $_valid_category    = array(
            'abstract',     'nightlife',    'nature',
            'animals',      'sports',       'technics',
            'city',         'fashion',      'transport',
            'food',         'people',
        );

    protected
        $_image_gray        = '',
        $_image_width,
        $_image_height,
        $_image_text,
        $_image_category,
        $_image_number,
        $_image_uri;

    public static function factory($width, $height, $gray = FALSE)
    {
        return new Lorempixel($width, $height, $gray);
    }

    public function __construct($width, $height, $gray = FALSE)
    {
        $this->width($width);
        $this->height($height);
        $this->gray($gray);
    }

    public function protocol($protocol = 'http')
    {
        $protocol = trim(strtolower($protocol));
        $this->_protocol = preg_match('#^https?#i', $protocol) ? $protocol : 'http';
        return $this;
    }

    public function https()
    {
        return $this->protocol('https');
    }

    public function url_only($bool)
    {
        $this->_uri_only = (bool) $bool;
        return $this;
    }

    public function attributes(array $attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    public function width($width)
    {
        $this->_not_null_or_zero($width, 'width');

        $this->_image_width = $width;
        return $this;
    }

    public function height($height)
    {
        $this->_not_null_or_zero($height, 'height');

        $this->_image_height = $height;
        return $this;
    }

    private function _not_null_or_zero($check, $field)
    {
        if (NULL == $check || 0 >= $check)
            throw new Kohana_Exception(':field should be greater than 0', array(
                ':field' => $field
            ));
    }

    public function gray($bool = TRUE)
    {
        $this->_image_gray = (TRUE === $bool ? 'g' : '');
        return $this;
    }

    public function text($text)
    {
        $this->_add_default_category();

        $this->_image_text = $text;
        return $this;
    }

    public function category($category)
    {
        $category = strtolower($category);

        if (in_array($category, $this->_valid_category))
        {
            $this->_image_category = $category;
        }
        return $this;
    }

    private function _add_default_category()
    {
        if (empty($this->_image_category))
        {
            $this->category(Lorempixel::random_pick($this->_valid_category));
        }

        return $this;
    }

    public function number($number)
    {
        $this->_not_null_or_zero($number, 'number');

        if ($number < 10)
        {
            $this->_add_default_category();

            $this->_image_number = $number;
        }

        return $this;
    }

    public function render()
    {
        $this->_image_uri = __(Lorempixel::LPX_PATH, array(
            ':width'    => $this->_image_width,
            ':height'   => $this->_image_height,
            ':gray'     => $this->_image_gray,
            ':protocol' => $this->_protocol,
        ));

        $this->_append('_image_category');
        $this->_append('_image_number');
        $this->_append('_image_text');

        if (TRUE === $this->_uri_only)
            return $this->_image_uri;

        return HTML::image($this->_image_uri, $this->_attributes);
    }

    private function _append($property)
    {
        if (! empty($this->$property))
        {
            $this->_image_uri .= "{$this->$property}/";
        }
    }

    public function __toString()
    {
        return $this->render();
    }

    public static function random_pick(array $array, $num = 1)
    {
        $keys   = array_rand($array, $num);
        $method = (!is_array($keys)) ? 'get' : 'extract';

        return Arr::$method($array, $keys);
    }
}