<?php

namespace Before;

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
  protected $name;

  //Before: hard coupling :-(,
  public function save()
  {
    $storage = new Storage();
    $storage->store();
    // ....
  }
}










namespace After;

interface Storage
{
  public function store();
}

class XmlStorage implements Storage
{
  public function store()
  {
    // ...logic to store data to XML file.
    print __CLASS__;
  }
  // ....
}

class MySqlStorage implements Storage
{
  public function store()
  {
    // ...logic to store data to MySQL.
    print __CLASS__;
  }
  // ....
}







class User
{
  protected $name = 'Berry';

  //After: free coupling = Dependency-Injection = :-)
  public function save(Storage $storage)
  {
    $storage->store();
    // ....
  }
}





$user = new User();

$user->save(new MySqlStorage());
