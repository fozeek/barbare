barbare
=======

framework for my own project


##Application

##Component

Ajouter dans la config
'components' => [
    ...
    'mycomponent' => function ($container) {
        // do stuff
    },
    'myothercomponent' => '\My\Class\Component',
    ...
]
You can load a component by closure or callable classname.
$container is an injection dependency container, it's contain all components of the application. Components are 'easyloading'.
The main component is 'application', this is the object of the full application.
If you write a callable classname, your component will be instanciated with $container as arg.
You can acces to your component in controller : 
$this->get('mycomponent');

And all the rest is as you wish ;)

##Helper

Ajouter dans la config
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
    public function __invoke($arg1, $arg2) {
        // Do stuuf
    }
}

If you write a class name for your helper, it has to be invokable with a public function named __invoke().



