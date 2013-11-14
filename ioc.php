<?php
/**
 * Inversion-of-Control
 *
 * Register an existing instance as a singleton.
 * <code>
 *    // Register an instance as a singleton in the container
 *    IoC::instance('mailer', new Mailer);
 * </code>
 *
 * Resolve a given type to an instance.
 * <code>
 *    // Get an instance of the "mailer" object registered in the container
 *    $mailer = IoC::resolve('mailer');
 *
 *    // Get an instance of the "mailer" object and pass parameters to the resolver
 *    $mailer = IoC::resolve('mailer', array('test'));
 * </code>
 *
 */
class IoC
{
  /**
   * Registered dependencies.
   * @var array
   */
  public static $registrat = array();

  /**
   * Resolved singleton instances.
   * @var array
   */
  public static $singletons = array();

  /**
   * Register an object and its resolver.
   * @param string $name
   * @param mixed $resolver
   * @param bool $singleton
   * @return void
   */
  public static function register($name, $resolver = null, $singleton = false)
  {
    if (is_null($resolver)) {
      $resolver = $name;
    }

    static::$registrat[$name] = compact('resolver', 'singleton');
  }

  /**
   * Unregister an object
   * @param string $name
   */
  public static function unregister($name)
  {
    if (isset(static::$registrat[$name])) {
      unset(static::$registrat[$name], static::$singletons[$name]);
    }
  }

  /**
   * Determine if an object has been registered.
   * @param string $name
   * @return bool
   */
  public static function registered($name)
  {
    return isset(static::$registrat[$name]) or isset(static::$singletons[$name]);
  }

  /**
   * Register an object as a singleton.
   * @param string $name
   * @param mixed $resolver
   * @return void
   */
  public static function singleton($name, $resolver = null)
  {
    static::register($name, $resolver, true);
  }

  /**
   * Register an existing instance as a singleton.
   * @param string $name
   * @param mixed $instance
   * @return void
   */
  public static function instance($name, $instance)
  {
    static::$singletons[$name] = $instance;
  }

  /**
   * Resolve a given type to an instance.
   * @param string $type
   * @param array $parameters
   * @return mixed
   */
  public static function resolve($type, $parameters = array())
  {
    if (isset(static::$singletons[$type])) {
      return static::$singletons[$type];
    }

    $concrete = $type;

    if (isset(static::$registrat[$type])
      and isset(static::$registrat[$type]['resolver'])) {
      $concrete = static::$registrat[$type]['resolver'];
    }

    if ($concrete == $type or $concrete instanceof Closure) {
      $object = static::build($concrete, $parameters);
    } else {
      $object = static::resolve($concrete);
    }

    if (isset(static::$registrat[$type]['singleton'])
      and static::$registrat[$type]['singleton'] === true) {
      static::$singletons[$type] = $object;
    }

    return $object;
  }

  /**
   * Instantiate an instance of the given type.
   *
   * @param  string $type
   * @param  array $parameters
   * @return mixed
   * @throws \Exception
   */
  protected static function build($type, $parameters = array())
  {
    if ($type instanceof Closure) {
      return call_user_func_array($type, $parameters);
    }

    $reflector = new ReflectionClass($type);

    if (!$reflector->isInstantiable()) {
      throw new Exception("Resolution target [$type] is not instantiable.");
    }

    $constructor = $reflector->getConstructor();

    if (is_null($constructor)) {
      return new $type;
    }

    $dependencies = static::dependencies($constructor->getParameters(), $parameters);

    return $reflector->newInstanceArgs($dependencies);
  }

  /**
   * Resolve all of the dependencies from the ReflectionParameters.
   * @param array $parameters
   * @param array $arguments that might have been passed into our resolve
   * @return array
   */
  protected static function dependencies($parameters, $arguments)
  {
    $dependencies = array();

    foreach ($parameters as $parameter) {

      $dependency = $parameter->getClass();

      if (count($arguments) > 0) {
        $dependencies[] = array_shift($arguments);
      } else if (is_null($dependency)) {
        $dependency[] = static::resolveNonClass($parameter);
      } else {
        $dependencies[] = static::resolve($dependency->name);
      }
    }

    return (array)$dependencies;
  }

  /**
   * Resolves optional parameters for our dependency injection.
   * @param ReflectionParameter $parameter
   * @return mixed
   * @throws Exception
   */
  protected static function resolveNonClass(ReflectionParameter $parameter)
  {
    if ($parameter->isDefaultValueAvailable()) {
      return $parameter->getDefaultValue();
    }

    throw new Exception("Unresolvable dependency resolving [$parameter].");
  }
}




/**
 * Testing Optional Parameters in classes' Dependency Injection
 */
class Animal
{
  public $name;

  public function __construct($name = 'Cheetah')
  {
    $this->name = $name;
  }
}

/**
 * Testing Dependency Injection with this class
 */
class Appe
{
  public $animal;

  public function __construct(Animal $animal)
  {
    $this->animal = $animal;
  }
}

/**
 * Testing Dependency Injection of ClassOne
 */
class Zoo
{
  public $appe;

  public function __construct(Appe $appe)
  {
    $this->appe = $appe;
  }
}


//IoC::register('Animal', function(){return new Animal('Tarzan');});

var_dump(

  IoC::resolve('Zoo')

);

