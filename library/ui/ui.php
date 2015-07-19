<?php


namespace Phiber\Ui;


use Phiber\phiber;

class ui extends phiber
{

    public $html;

    public function __construct()
    {
        $this->html = html::createElement();
    }

    public function createScript($path, array $options = array(), $base = null)
    {
        if (isset($base)) {
            $path = $base.$path;
        }
        $options['src'] = $path;
        return $this->createTag('script', $options, null);
    }
    public function createHeaderLink($path, array $options = array(), $base = null)
    {
        if (isset($base)) {
            $path = $base.$path;
        }
        $options['href'] = $path;
        if (!array_key_exists('rel', $options)) {
            $options['rel'] = 'stylesheet';
        }
        if (!array_key_exists('type', $options)) {
            $options['type'] = 'text/css';
        }
        return $this->createTag('link', $options, '');
    }
    public function createTag($element, array $options = array(), $content = ' ')
    {
        $element = html::createElement($element);
        $prefix = 'phi-header-';
        if (array_key_exists('prefix', $options)) {
            $prefix = $options['prefix'];
            unset($options['prefix']);
        }
        $this->view->idCount[$prefix][] = $element->getTag();

        $id =  $prefix . count($this->view->idCount[$prefix]);

        if (!empty($options)) {
            foreach($options as $name => $value){
                $element->set($name, $value);
            }
        }

        $element->setText($content);

        $element->id($id);
        return $element;
    }
}