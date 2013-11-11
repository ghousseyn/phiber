codup
=====

A lightweight MVC PhP framework featuring:

- A comprehensive router 

``` apache
# for this ugly url
  http://127.0.0.1/dev/index/action/var1/va%20l1?var%202=v%20al2/?var3/val3

# the router will pass your application an array like

Array
(
    [module] => dev
    [controller] => index
    [action] => action
    [vars] => Array
        (
            [var1] => va l1
            [var_2] => v al2
            [var3] => val3
        )

)

```

``` php
  //to get var1 for instance in your controller

  $var = $this->_request('var1');
  
  //Check request type
  
  if($this->isPost()){
  
  }
  
  /* 
   * For ajax requests the default behaviour is to disable the layout if there is any 
   * along with action template unless the 'html' context is specified
   * in which case only the action emplate is returned
   */
  if($this->isAjax()){
  
  }
  
```

- Easily extensible with modules/plugins (automatically loaded and extremely easy to create) 

``` php
  //For modules just create a folder under modules directory and toss your controllers there
  // all classes should extend main and files with

  $this->view->variable = "some text";
  
  //in the template 
  
  $this->variable;
```
- ZF like templating

``` php
  //from your controller

  $this->view->variable = "some text";
  
  //in the template 
  
  $this->variable;
```

- Ajax context aware (html, plain-text or json)
- and more other cool features to come.

This project is under havey developement and still in alpha stage. Please come back soon, and follow us so we can notify you 
as soon as we get to the first release. 
Needless to say, any help is much appreciated.



