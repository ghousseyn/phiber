<?php
namespace Phiber;
class controller
{

    protected $phiber;
    protected $view;

    public function __construct(phiber $phiber)
    {
        $this->phiber = $phiber;
        $this->view = $this->phiber->view;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->phiber, $name)) {
            return call_user_func_array(array($this->phiber, $name), $arguments);
        }
    }
    public function __get($name)
    {
        if (property_exists($this->phiber, $name)) {
            return $this->phiber->{$name};
        }
    }
}
