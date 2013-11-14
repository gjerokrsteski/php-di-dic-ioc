<?php
namespace Before;

//Before: hard coupling :-(
class Storage
{
  public function store()
  {
    // store it to a XML file.
  }
  // ....
}

class User
{
  protected $_name;

  public function save()
  {
    $storage = new Storage();
    $storage->store();
    // ....
  }
}


namespace After;

//After: free coupling = Dependency-Injection = :-)
interface Storage
{
  public function store();
}

class XmlStorage implements Storage
{
  public function store()
  {
    // ...logic to store data to XML file.
  }
  // ....
}

class MySqlStorage implements Storage
{
  public function store()
  {
    // ...logic to store data to MySQL.
  }
  // ....
}

class User
{
  protected $name = 'Bob';

  public function save(Storage $storage)
  {
    $storage->store();
    // ....
  }
}


$user = new User();

$user->save(new MySqlStorage());
