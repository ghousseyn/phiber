[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ghousseyn/phiber/badges/quality-score.png?s=436bbca471c3881b34e0c2d36b311c003aea5739)](https://scrutinizer-ci.com/g/ghousseyn/phiber/) [![Build Status](https://travis-ci.org/ghousseyn/phiber.png?branch=alpha)](https://travis-ci.org/ghousseyn/phiber) [![Code Coverage](https://scrutinizer-ci.com/g/ghousseyn/phiber/badges/coverage.png?s=6282beaf967e0ac820a325b6897fab427f286908)](https://scrutinizer-ci.com/g/ghousseyn/phiber/)


![Phiber Framework](logo.png)

Phiber
=====


Phiber is a lightweight MVC PhP framework featuring:

- A comprehensive router with zero configuration required

```
# for this url for exmaple

  http://yoursite.com/dev/index/action/var1/val1?var2=val2/?var3/val3

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
  //to get var1 for instance in your controller being it POST or GET (AJAX calls also)

  $var1 = $this->_request('var1');
  
  //Check request type
  
  if($this->isPost()){
  
  }
  
  /* 
   * For ajax requests you can disable the layout if there is any 
   * along with action template unless the 'html' context is specified
   * in which case only the action template is returned
   */
  if($this->isAjax()){
  	$this->disableLayout();
  }
  
```

- Easily extensible with modules/plugins (automatically loaded and extremely easy to create) 

``` php
  /* 
  * For modules just create a folder under modules directory and toss your
  * controllers there all class files in the format {classname}.php will be
  * accessible as controllers 
  * you don't need the word controller in the class name and your actions
  * doesn't need the word action neither
  */
   
   //file: /modules/firstmod/cool.php
   
  class cool extends Phiber\controller {

    function index(){
      $this->view->message = "message";
    }
    function action(){
      $this->view->title = "Cool title from action";
    }
  }
  // if this is in a directory called firstmod 
  // http://localhost/firstmod/cool executes the index method (default method configurable)
  // http://localhost/firstmod/cool/action will fire the action method
  // and so on
  
```

``` php

  /*
  *  The template should be in a subdirectory under views called 'cool' with
  *  template files in it mapping the actions
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
  * For plugins just create a folder named after your plugin and place it in the 
  * plugins folder
  * follwing the same conventions your plugin loader would be expected to be in
  * the format: {pluginName}.php
  * The class should also extend Phiber\plugin 
  * The run() method is the entry point and should contain code to 
  * initialize/execute your plugin
  */
  
  //file: /plugins/coolplugin/coolplugin.php
  
  class coolplugin extends Phiber\plugin {

    function run(){
      
      /* Your usefull code here */
      
    }
  }
  
```
- ZF like templating

``` php
  /**
  * from your controller
  * /

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
    
  if($trueCondition){
          
        /* do some processing */
  }
    
  $this->view->msg1 = "result one";
  $this->view->someothervar = "some cool findings";
    
  //Then switch to the 'html' context to send the contents of the template file as it is (no layout)
  
  if($this->isAjax()){
        $this->contextSwitch('html');
  }

```
- An ORM and a query builder to handle database interactions with a relation-aware model class generator (currently supporting only MySQL)

```php

/**
* for a table "blog_post" an entity file with the class blog_post will be
* generated for you using the generate tool.
* Edit the generate.php file and put in your db credentials
* you can specify the path where the files should be saved using the $gen->path
* property.
* Then execute generate.php in cli and move the created files to the entity dir
* if you specified a different path.
* The tool will inspect your database and identify Primary keys (composite too)
* foreign keys (relations) and the other columns
* Each table will have its own entity class file
* /

// From your model (in models folder)
// create file: /models/blog_post.php
```
```php
namespace models;
use Phiber;
use entity;

class blog_post extends Phiber\model
{
   
  // example model method
   
   public funciton getPost($id){
      $post = entity\blog_post::getInstance();
      return $post->find($id)->fetch();
   }
   .... Any domain specific logic can be put here too
}

//within your model you can do more:  

// create new

   $post->title = "Great tips";
   $post->link = "http://www.somelink.com";
   $post->save();
   
// fetch existing
// get post with primary key = 0 (regardless of the name of your primary key)

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
    * This will generate an update query to the user where the user_id from the
    * previous query matches
    * No EXTRA selects
    * but when needed just call the load() method
    */

	$user = $user->load(); 
    
	// user data is loaded now and can be displayed or manipulated then saved
    
	 echo $user->alias;
     
     /**
     *   Joins
     */
      /*
          * Tables have defined relations (innodb)
          *
          * SELECT     dept_manager.dept_no,
          * 		dept_manager.emp_no,
          * 		dept_manager.from_date,
          * 		dept_manager.to_date ,
          * 		departments.dept_name ,
          * 		employees.last_name
          * FROM 	dept_manager
          *     JOIN 	departments
          *       ON 		dept_manager.dept_no = departments.dept_no
          *     JOIN 	employees
          *       ON 		dept_manager.emp_no = employees.emp_no
          * WHERE dept_manager.emp_no = 110039
          */
          
            
        $res = $dept_manager->find(110039)
							->with(array('departments' => array("dept_name")))
							->with(array('employees' => array("last_name")))
							->fetch();
	 
     /**
     * From your controllers you can either use your models
     */
     $post = models\blog_post::getInstance()->getPost($id);
     
     /**
     * Or use the entities directly
     */
     $post = entity\blog_post::getInstance()->find($id)->fetch();
```

- Log Errors, debug messages and display them however you want 

```php
   class index extends Phiber\controller
{

  public function main()
  {
    $variableOne = 'test';

    $variableTwo = 'var';

    $variableThree = 32156;

    echo $undefined;

    $this->view->message = "This is dev module controller - welcome to Phiber";

    $this->get('log')->notice('hello from index!:' . __line__);

    $vars = 'scope test';

    $log2 = $this->setLog('file', 'second', 'secondlog');

    $this->get('log')->notice('hello from index!:' . __line__, array('message' => $this->view->message));

    $log2->info('hello from index!:' . __line__);

    $this->logger()->debug('second hello from index!:' . __line__);

    $this->get('secondlog')->debug('second hello from index!:' . __line__);

    trigger_error(' A triggered E_USER_WARNING', E_USER_WARNING);
  }
}

// In debug mode (set globally in config.php or dynamically in runtime)
// The above code will output this to your browser

Notice:Undefined variable: undefined

Code:8 File:{PATH}/library/modules/dev/index.php:14

Vars:

 $variableOne = test
 $variableTwo = var
 $variableThree = 32156

Trace:

0. index->main() {PATH}/library/main.php:235

1. Phiber\main->dispatch() {PATH}/library/main.php:82

2. Phiber\main->run() {PATH}/site/index.php:6

[notice] hello from index!:18

Array
(
    [message] => This is dev module controller - welcome to Phiber
)

[info] hello from index!:26

[debug] second hello from index!:28

[debug] second hello from index!:30

Warning: A triggered E_USER_WARNING

Code:512 File:{PATH}library/modules/dev/index.php:32

Vars:

 $variableOne = test
 $variableTwo = var
 $variableThree = 32156
 $vars = scope test
 $log2 = Instance of logger\file

Trace:

0. trigger_error() {PATH}/library/modules/dev/index.php:32

  Vars:
  A triggered E_USER_WARNING
  512

1. index->main() {PATH}/library/main.php:235

2. Phiber\main->dispatch() {PATH}/library/main.php:82

3. Phiber\main->run() {PATH}/site/index.php:6

```

- and more other cool features to come.

This project is under heavy developement and still in alpha stage. Please come back soon.

Needless to say, any help is much appreciated.

We try to keep up with [Semantic Versioning](http://semver.org/)

