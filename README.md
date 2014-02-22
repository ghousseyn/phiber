codup
=====


A lightweight MVC PhP framework featuring:

- A comprehensive router 

```
# for this url for exmaple

  http://127.0.0.1/dev/index/action/var1/val1?var2=val2/?var3/val3

# the router will pass your application an array (for GET and POST and Ajax calls alike)

Array
(
    [module] => dev
    [controller] => index
    [action] => action
    [vars] => Array
        (
            [var1] => val1
            [var2] => val2
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
   * in which case only the action template is returned
   */
  if($this->isAjax()){
  
  }
  
```

- Easily extensible with modules/plugins (automatically loaded and extremely easy to create) 

``` php
  /* For modules just create a folder under modules directory and toss your controllers there
   * all class files in the format {classname}.php will be accessible as controllers 
   * you don't need the word controller in the class name and your actions
   * doesn't need the word action neither
   */
   
   //file: /modules/firstmod/cool.php
   
  class cool extends main {

    function index(){
      $this->view->message = "message";
    }
    function action(){
      $this->view->title = "Cool title from action";
    }
  }
  // if this is in a directory called firstmod 
  // the url http://localhost/firstmod/cool will fire the index method
  // the url http://localhost/firstmod/cool/action will fire the action method
  // and so on
  
```

``` php

  /*
  *  The template should be in a subdirectory under views called 'cool' with template files
  *  in it mapping the actions
  *  so for our controller we need an 'index.php' and an 'action.php'
  */

  /*
  * file: /modules/firstmod/views/cool/index.php
  */
  
  <div class="cool-msg-style">
    <?php echo $this->message ?>
  </div>
```

``` php
  /*
  * file: /modules/firstmod/views/cool/action.php
  */
  <div class="cool-title-style">
    <?php echo $this->title ?>
  </div>
```

``` php
  // Plugins
  
  /*
  * For plugins just create a folder named after your plugin and place it in the plugins folder
  * follwing the same conventions your plugin loader would be expected to be in {pluginName}.php
  * The class should also extend main 
  * The run() method is the entry point and should contain code to initialize/execute your plugin
  */
  
  //file: /plugins/coolplugin/coolplugin.php
  
  class coolplugin extends main {

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
    
    $this->sendJSON(array('success'=>true));
    
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
    
  //Then switch to the 'html' context to send the contents of the template file as it is (no layout)
  
  if($this->isAjax()){
        $this->contextSwitch('html');
  }

```
- An ORM and a query builder to handle database interactions with a relation-aware model class generator

``` php

// for a table "blog_post" a file with the class blog_post will be generated for you

   $post = models\blog_post::getInstance();
   
// create new

   $post->title = "Great tips";
   $post->link = "http://www.somelink.com";
   $post->save();
   
// fetch existing
//get post with primary key = 0 (regardless of the name of your primary key)

   $result = $post->find(0)->fetch();  // Careful when using composite primary keys coz this will much only the first member

// OR
   
   $result = $post->find(array('user_id' =>14))->limit(0,5)->fetch(); // fetchs 5 results
   
// OR

   $result = $post->select()->where('user_id = ?',14)->limit(0,5)->fetch(); // same as previous
      
// $result is a collection

   //show results
   
   		foreach ($result as $r){
          
          	echo "Title: $r->title ";

        }
         
   //change results (only changed columns will be included in the query)
   
    	foreach ($result as $r){
    
    		$r->title = "$r->title (great)";
    		$r->save();
    	}
    /*
    *	If table "blog_post" has a constraint fk to other tables like "user" for example
    *	you can either join on select using with() (eager loading)
    */
    
    $result = $post->select()
    			   ->where('user_id = ?',14)
    			   ->with(array('user' => array("col1","col2"))) // not specifying any columns will select user.*
    			   ->limit(0,5)->fetch();
    			   
    // $result is a collection instance (you can search within it, sort it and do a lot of things)
    
    $result->sortByProperty('rating'); // defaults to regular sort (other sorting options are available)
     
    /*
    *	or load it when needed (lazy loading) 
    *	by calling a virtual method named after the related table like this
    */			   
    
    $user = $result[0]->user(); 
    
    
    
    /*
    *	The good thing about this is that data from "user" table is not loaded yet
    *	but you can rather update the related record without fetching it (neat)	 
    *	something like this:
    */
    
    $user->banned = "1";
    $user->active = 0;
    $user->save();
    
    /*
    *	This will generate an update query to the user where the user_id from the previous query matches
    *	No EXTRA selects
    * 	bu when needed just call the load() method
    */

	$user = $user->load(); // user data is loaded now and can be displayed or manipulated then saved
	
	 echo $user->alias;
	 
```

- and more other cool features to come.

This project is under heavy developement and still in alpha stage. Please come back soon.

Needless to say, any help is much appreciated.

We try to keep up with [Semantic Versioning](http://semver.org/)

