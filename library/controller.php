<?php
namespace Phiber;
class controller
{

    protected $phiber;

    public function __construct(phiber $phiber)
    {
        $this->phiber = $phiber;
        $this->phiber->view;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->phiber, $name)) {
            return call_user_func_array([$this->phiber, $name], $arguments);
        }
    }
}
