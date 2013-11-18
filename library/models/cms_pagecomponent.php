<?php
namespace models;
use Codup;
class cms_pagecomponent extends model  
{
    public $id;
    public $cms_page_id;
    public $cms_component_id;
    public $template_spot;
    public $ord;
    public function getPrimary() 
    {
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
    public function getRelations() 
    {
        return array('cms_page_id'=>'cms_page.id','cms_component_id'=>'cms_component.id',);
    }
}
