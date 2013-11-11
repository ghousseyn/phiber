codup
=====

A lightweight MVC PhP framework featuring:

- A comprehensive router 

```
# for this ugly url
  http://127.0.0.1/dev/index/action/var1/va%20l1?var%202=v%20al2/?var3/val3

# the router will pass your application an array like (for GET and POST and Ajax calls alike)

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
  //to get var1 for instance in your controller (POST and AJAX calls also)

  $var1 = $this->_request('var1');
  
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
  /* For modules just create a folder under modules directory and toss your controllers there
   * all class files in the format class.{classname}.php will be accessible as controllers 
   * classes should extend main and implement static function getInstance()
   * you don't need the word controller in the class name and your actions doesn't need the word action neither
   */
   
   //file: /modules/cool/class.cool.php
   
  class cool extends main {
    static function getInstance(){
      return new self;
    }
    function index(){
      $this->view->message = "message";
    }
  }
  // if this is in a directory called firstmod the url http://localhost/firstmod/cool will fire the index method
  
  /*
  *  The template should be in a subdirectory under views called 'cool' with template files in it mapping the actions
  *  so for our controller we need a file called 'index.php' containing what ever html you like
  */

  //file: /modules/cool/views/cool/index.php
  
  <div class="cool-style">
    <?php echo $this->message ?>
  </div>
```

``` php
  // Plugins
  
  /*
  * For plugins just create a folder named after your plugin and place it in the plugins folder
  * follwing the same conventions your plugin loader would be expected to be in class.{pluginName}.php
  * The class should also extend main and implement the static function getInstance()
  * The run() method is the entry point and should hold the code to initialize and execute your plugin
  */
  
  //file: /plugins/coolplugin/class.coolplugin.php
  
  class coolplugin extends main {
    static function getInstance(){
      return new self;
    }
    
    function run(){
      
      /* Your usefull code here */
      
    }
  }
  
```
- ZF like templating

``` php
  //from your controller

  $this->view->variable = "some text";
  
  //in the template 
  
  $this->variable;
```

- Ajax context aware (html, plain-text or json)

``` php
  //check for ajax calls

  if($this->isAjax()){
    
    //send what you want through ajax
    
    echo "text here";
    
    //or json
    
    echo json_encode(array('success'=>true));
    
    /*
    
    {"success":true}
    
    */
    
    }
    
  //alternatively you could just leave your action as it is
    
  if(true){
          
        /* do some processing */
  }
    
  $this->view->msg1 = "result one";
  $this->view->someothervar = "some cool findings";
    
  //Then switch to the 'html' context to send the contents of the corresponding template file as it is (no layout)
  
  if($this->isAjax()){
        $this->contextSwitch('html');
  }

```
- and more other cool features to come.

This project is under havey developement and still in alpha stage. Please come back soon, and follow us so we can notify you 
as soon as we get to the first release. 
Needless to say, any help is much appreciated.



