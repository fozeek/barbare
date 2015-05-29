Barbare
=======

framework for my own project


## Application

### services


#### routes
It provides all routes for the application.
##### router
```php
$app->getService('router');
```
Retrieve an url by name
```php
$router->url('route_name', ['arg' => 'value']);
```
Retrieve an route object by url
The object will be filled with the callback property and all arguments.
```php
$router->findRoute('/link/to/my/page.html');
```
##### route
Name of the route
```php
$route->getFullName()
```
Match with an url. Will return this object or an child route if found. false otherwise.
```php
$route->match('/link/to/my/page.html');
```
Get url
```php
$route->getFullUrl(['arg' => 'value']);
```
Get callback
```php
$route->getCallback();
```
Get param
```php
$route->getParam($name);
```

#### request
```php
$app->getService('request');
```
Get route. Return the actuel route used
```php
$request->getRoute();
```
Return data form request ($_REQUEST & $_FILES)
```php
$request->getData();
```
Match the request method
```php
$request->is('post');
```



## Containers

### Component

Ajouter dans la config
```php
'components' => [
    ...
    'mycomponent' => function ($container) {
        // do stuff
    },
    'myothercomponent' => '\My\Class\Component',
    ...
]
Class Component {
    public function construct($container) {
        // Do stuff
    }
}
```
You can load a component by closure or callable classname.
```$container``` is an injection dependency container, it's contain all components of the application.
The main component is 'application', this is the object of the full application.
If you write a callable classname, your component will be instanciated with $container as arg.
You can acces to your component in controller : 
```php
$this->get('mycomponent');
```

And all the rest is as you wish ;)

### Helper

Ajouter dans la config
```php
'components' => [
    ...
    'myhelper' => function ($container) {
        return function($arg1, $arg2) {
            // do stuff
        }
    },
    'myotherhelper' => '\My\Class\Helper',
    ...
]

Class Helper {
    public function construct($container) {
        // Do stuff
    }
    public function __invoke($arg1, $arg2) {
        // Do stuff
    }
}
```
If you write a class name for your helper, it has to be invokable with a public function named __invoke().
$container arg in ```__construct``` method, contain all helper in Injection Dependancy Container.
List of defaults services in container :
 * view : The main view of the application




