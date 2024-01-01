<?php
namespace acfLocalJsonLocator;

use Exception;

/**
 * Singleton base class for having a singleton implementation.
 *
 * Class Singleton
 * @package jbarke/acf-json-locator
 */
abstract class Singleton
{
  /**
   * The Singleton’s instance is stored in a static field. This field is an
   * array, because our Singleton can have subclasses. Each item in
   * this array will be an instance of a specific Singleton’s subclass.
   */
  private static $instances = [];

  /**
   * The Singleton’s constructor should always be private to prevent direct
   * construction calls with the `new` operator.
   * To use the Singleton, one has to obtain the instance from the
   * `Singleton::getInstance()` method.
   *
   * @return void
   */
  private function __construct ()
  {
  }

  /**
   * Singletons should not be cloneable.
   *
   * @return void
   */
  private function __clone ()
  {
  }

  /**
   * Singletons should not be restorable from strings.
   *
   * @return void
   */
  final public function __wakeup ()
  {
    throw new \Exception("Cannot unserialize a singleton.");
  }

  /**
   * This implementation lets you subclass the Singleton class while keeping
   * just one instance of each subclass around.
   */
  final public static function getInstance (): Singleton
  {
    $cls = static::class;

    if (!isset(self::$instances[$cls])) {
      self::$instances[$cls] = new static();
    }

    return self::$instances[$cls];
  }
}
