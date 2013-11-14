<?php
class DependencyInjectionContainer
{
  protected $dependencies = array();

  public function __set($identifier, $instance)
  {
    $this->dependencies[$identifier] = $instance;
  }

  public function __get($identifier)
  {
    return $this->dependencies[$identifier]($this);
  }
}

class Mail
{
  public $username, $email;

  public function __construct($email, $username)
  {
    $this->email    = $email;
    $this->username = $username;
  }
}

$dic = new DependencyInjectionContainer();

$dic->mailer_class    = function () {
  return 'Mail';
};
$dic->mailer_username = function () {
  return 'Bob';
};
$dic->mailer_email    = function () {
  return 'bob@gmx.de';
};

$dic->mailer = function (DependencyInjectionContainer $dic) {
  return new $dic->mailer_class($dic->mailer_email, $dic->mailer_username);
};



print_r($dic->mailer);



/**
 * Yet Another (PHP) Dependency Injection Framework
 * @link https://github.com/tsmckelvey/yadif
 */


